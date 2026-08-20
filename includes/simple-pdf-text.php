<?php
/**
 * Lightweight pure-PHP PDF text extractor for text-based dispatch PDFs.
 *
 * Supports Flate-compressed content streams, Type0 Identity-H fonts and
 * ToUnicode CMaps. It intentionally does not perform OCR.
 */

if ( ! defined( 'ABSPATH' ) ) {
    exit;
}

final class FFL_Simple_PDF_Text {
    /** @var array<int,string> */
    private $objects = array();

    /** @var array<string,array<string,string>> */
    private $font_maps = array();

    public function extract_file( $path ) {
        $pdf = @file_get_contents( $path );
        if ( false === $pdf || '' === $pdf ) {
            throw new RuntimeException( 'PDF file could not be read.' );
        }

        $this->objects = $this->parse_objects( $pdf );
        $this->font_maps = $this->build_font_maps();

        $parts = array();
        foreach ( $this->objects as $object ) {
            $stream = $this->decode_stream( $object );
            if ( '' === $stream || false === strpos( $stream, 'BT' ) ) {
                continue;
            }
            $text = $this->extract_content_stream( $stream );
            if ( '' !== trim( $text ) ) {
                $parts[] = $text;
            }
        }

        $text = trim( implode( "\n", $parts ) );
        $text = preg_replace( "/[ \t]+\n/", "\n", $text );
        $text = preg_replace( "/\n{3,}/", "\n\n", $text );
        return trim( (string) $text );
    }

    private function parse_objects( $pdf ) {
        $objects = array();
        if ( preg_match_all( '/(?:^|\r?\n)(\d+)\s+\d+\s+obj\b(.*?)\bendobj\b/s', $pdf, $matches, PREG_SET_ORDER ) ) {
            foreach ( $matches as $match ) {
                $objects[ (int) $match[1] ] = $match[2];
            }
        }
        return $objects;
    }

    private function decode_stream( $object ) {
        $pos = strpos( $object, 'stream' );
        if ( false === $pos ) {
            return '';
        }
        $start = $pos + 6;
        if ( isset( $object[ $start ] ) && "\r" === $object[ $start ] ) $start++;
        if ( isset( $object[ $start ] ) && "\n" === $object[ $start ] ) $start++;
        $end = strrpos( $object, 'endstream' );
        if ( false === $end || $end <= $start ) return '';
        $data = substr( $object, $start, $end - $start );

        if ( false !== strpos( substr( $object, 0, $pos ), '/FlateDecode' ) ) {
            $decoded = @gzuncompress( $data );
            if ( false === $decoded ) $decoded = @gzinflate( $data );
            if ( false === $decoded && strlen( $data ) > 2 ) $decoded = @gzinflate( substr( $data, 2 ) );
            if ( false === $decoded ) return '';
            $data = $decoded;
        }
        return (string) $data;
    }

    private function build_font_maps() {
        $maps = array();
        foreach ( $this->objects as $object ) {
            if ( false === strpos( $object, '/Type' ) || false === strpos( $object, '/Font' ) ) continue;
            if ( ! preg_match( '/\/ToUnicode\s+(\d+)\s+\d+\s+R/', $object, $m ) ) continue;
            $cmap_obj = $this->objects[ (int) $m[1] ] ?? '';
            $cmap = $this->decode_stream( $cmap_obj );
            if ( '' === $cmap ) continue;
            $font_map = $this->parse_cmap( $cmap );
            if ( ! empty( $font_map ) && preg_match( '/\/BaseFont\s*\/([^\s\/]+)/', $object, $base ) ) {
                $maps[ $base[1] ] = $font_map;
            }
        }

        // Connect page resource names (/F0, /F1...) to the corresponding BaseFont map.
        $resource_maps = array();
        foreach ( $this->objects as $object ) {
            if ( preg_match_all( '/\/(F\d+)\s+(\d+)\s+\d+\s+R/', $object, $matches, PREG_SET_ORDER ) ) {
                foreach ( $matches as $m ) {
                    $font_obj = $this->objects[ (int) $m[2] ] ?? '';
                    if ( preg_match( '/\/BaseFont\s*\/([^\s\/]+)/', $font_obj, $base ) && isset( $maps[ $base[1] ] ) ) {
                        $resource_maps[ $m[1] ] = $maps[ $base[1] ];
                    }
                }
            }
        }
        return $resource_maps;
    }

    private function parse_cmap( $cmap ) {
        $map = array();
        if ( preg_match_all( '/<([0-9A-Fa-f]+)>\s*<([0-9A-Fa-f]+)>/', $cmap, $pairs, PREG_SET_ORDER ) ) {
            foreach ( $pairs as $pair ) {
                $source = strtoupper( $pair[1] );
                $target = @hex2bin( $pair[2] );
                if ( false === $target ) continue;
                if ( 0 === strlen( $target ) % 2 ) {
                    $utf8 = @iconv( 'UTF-16BE', 'UTF-8//IGNORE', $target );
                } else {
                    $utf8 = $target;
                }
                if ( false !== $utf8 && '' !== $utf8 ) $map[ $source ] = $utf8;
            }
        }
        return $map;
    }

    private function extract_content_stream( $stream ) {
        $out = '';
        $font = '';
        $tokens = preg_split( '/(?=\/F\d+\s+[\d.]+\s+Tf)|(?=BT\b)|(?=ET\b)|(?=[\d.\-]+\s+[\d.\-]+\s+Td\b)|(?=\[(?:.|\n)*?\]\s*TJ)|(?=<[0-9A-Fa-f]+>\s*Tj)|(?=\((?:\\.|[^\\)])*\)\s*Tj)/s', $stream, -1, PREG_SPLIT_NO_EMPTY );
        foreach ( $tokens as $token ) {
            if ( preg_match( '/^\/(F\d+)\s+[\d.]+\s+Tf/', $token, $m ) ) {
                $font = $m[1];
            }
            if ( preg_match( '/^([\d.\-]+)\s+([\d.\-]+)\s+Td/', $token, $m ) ) {
                if ( '' !== $out && (float) $m[2] < 0 ) $out .= "\n";
            }
            if ( preg_match( '/^\[(.*?)\]\s*TJ/s', $token, $m ) ) {
                $out .= $this->decode_text_array( $m[1], $font );
            } elseif ( preg_match( '/^<([0-9A-Fa-f]+)>\s*Tj/', $token, $m ) ) {
                $out .= $this->decode_hex_text( $m[1], $font );
            } elseif ( preg_match( '/^\(((?:\\.|[^\\)])*)\)\s*Tj/s', $token, $m ) ) {
                $out .= $this->decode_literal( $m[1] );
            }
            if ( preg_match( '/^ET\b/', $token ) && '' !== $out && "\n" !== substr( $out, -1 ) ) $out .= "\n";
        }
        return $out;
    }

    private function decode_text_array( $body, $font ) {
        $text = '';
        if ( preg_match_all( '/<([0-9A-Fa-f]+)>|\(((?:\\.|[^\\)])*)\)|(-?\d+(?:\.\d+)?)/s', $body, $items, PREG_SET_ORDER ) ) {
            foreach ( $items as $item ) {
                if ( '' !== ( $item[1] ?? '' ) ) $text .= $this->decode_hex_text( $item[1], $font );
                elseif ( '' !== ( $item[2] ?? '' ) ) $text .= $this->decode_literal( $item[2] );
                elseif ( isset( $item[3] ) && (float) $item[3] < -180 ) $text .= ' ';
            }
        }
        return $text;
    }

    private function decode_hex_text( $hex, $font ) {
        $hex = strtoupper( $hex );
        $map = $this->font_maps[ $font ] ?? array();
        if ( ! empty( $map ) ) {
            $text = '';
            $step = 4;
            for ( $i = 0, $len = strlen( $hex ); $i < $len; $i += $step ) {
                $code = substr( $hex, $i, $step );
                $text .= $map[ $code ] ?? '';
            }
            return $text;
        }
        $raw = @hex2bin( $hex );
        if ( false === $raw ) return '';
        if ( 0 === strlen( $raw ) % 2 && preg_match( '/^(?:\x00.|[\x01-\xFF].)+$/s', $raw ) ) {
            $utf8 = @iconv( 'UTF-16BE', 'UTF-8//IGNORE', $raw );
            if ( false !== $utf8 ) return $utf8;
        }
        return @iconv( 'Windows-1252', 'UTF-8//IGNORE', $raw ) ?: $raw;
    }

    private function decode_literal( $value ) {
        $value = preg_replace_callback( '/\\\\([0-7]{1,3})/', static function( $m ) { return chr( octdec( $m[1] ) ); }, $value );
        $value = strtr( $value, array( '\\n' => "\n", '\\r' => "\r", '\\t' => "\t", '\\b' => "\b", '\\f' => "\f", '\\(' => '(', '\\)' => ')', '\\\\' => '\\' ) );
        return @iconv( 'Windows-1252', 'UTF-8//IGNORE', $value ) ?: $value;
    }
}
