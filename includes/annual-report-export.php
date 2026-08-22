<?php
if ( ! defined( 'ABSPATH' ) ) { exit; }

function ffl_annual_report_years() {
    global $wpdb;
    $values = $wpdb->get_col( $wpdb->prepare( "SELECT meta_value FROM {$wpdb->postmeta} WHERE meta_key = %s AND meta_value <> ''", '_ffl_alarmzeit' ) );
    $years = array();
    foreach ( $values as $value ) {
        if ( preg_match( '/^(\d{4})-/', (string) $value, $match ) ) { $years[] = (int) $match[1]; }
    }
    $years = array_values( array_unique( array_filter( $years ) ) );
    rsort( $years, SORT_NUMERIC );
    return $years ?: array( (int) wp_date( 'Y' ) );
}

function ffl_annual_report_incidents( $year ) {
    return get_posts( array(
        'post_type' => 'ffl_einsatz', 'post_status' => 'publish', 'posts_per_page' => -1,
        'meta_query' => array( array( 'key' => '_ffl_alarmzeit', 'value' => sprintf( '%04d-', absint( $year ) ), 'compare' => 'LIKE' ) ),
        'meta_key' => '_ffl_alarmzeit', 'orderby' => 'meta_value', 'order' => 'ASC',
    ) );
}

function ffl_annual_report_rows( $year ) {
    $rows = array();
    foreach ( ffl_annual_report_incidents( $year ) as $incident ) {
        $id = $incident->ID;
        $alarm = (string) get_post_meta( $id, '_ffl_alarmzeit', true );
        $style = ffl_term_style( $id );
        $rows[] = array(
            'number' => ffl_get_einsatz_number( $id ),
            'date' => $alarm ? wp_date( 'd.m.Y', strtotime( $alarm ) ) : '',
            'time' => $alarm ? wp_date( 'H:i', strtotime( $alarm ) ) : '',
            'title' => get_the_title( $id ),
            'type' => ffl_term_display_name( $style['name'] ),
            'keyword' => (string) get_post_meta( $id, '_ffl_alarmstichwort', true ),
            'location' => (string) get_post_meta( $id, '_ffl_einsatzort', true ),
            'duration' => ffl_get_duration( $id ),
            'vehicles' => implode( ', ', ffl_parse_list( get_post_meta( $id, '_ffl_fahrzeuge', true ) ) ),
        );
    }
    return $rows;
}

function ffl_annual_report_page_url( $year = 0, $args = array() ) {
    $base = add_query_arg( array( 'post_type' => 'ffl_einsatz', 'page' => 'ffl_einsatz_jahresbericht' ), admin_url( 'edit.php' ) );
    if ( $year ) { $args['year'] = absint( $year ); }
    return add_query_arg( $args, $base );
}

function ffl_render_annual_report_page() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) ); }
    $years = ffl_annual_report_years();
    $year = absint( $_GET['year'] ?? $years[0] );
    $stats = ffl_get_year_statistics( $year );
    $token = sanitize_key( $_GET['ffl_report_token'] ?? '' );
    $error = sanitize_text_field( wp_unslash( $_GET['ffl_report_error'] ?? '' ) );
    $ready = false;
    $download_url = '';
    $filename = '';
    if ( $token ) {
        $data = get_transient( 'ffl_annual_report_' . get_current_user_id() . '_' . $token );
        if ( is_array( $data ) && ! empty( $data['path'] ) && is_file( $data['path'] ) ) {
            $ready = true;
            $filename = $data['name'];
            $download_url = wp_nonce_url( add_query_arg( array( 'action' => 'ffl_download_annual_report', 'token' => $token ), admin_url( 'admin-post.php' ) ), 'ffl_download_annual_report_' . $token );
        }
    }
    ?>
    <div class="wrap">
        <h1><?php echo esc_html( ffl_lang( 'Jahresbericht exportieren', 'Export Annual Report' ) ); ?></h1>
        <p><?php echo esc_html( ffl_lang( 'Erstellt den Jahresbericht zunächst als Datei auf dem Server. Danach erscheint ein sicherer Download-Link.', 'Creates the annual report as a server-side file first. A secure download link is then displayed.' ) ); ?></p>
        <?php if ( $error ) : ?>
            <div class="notice notice-error"><p><strong><?php echo esc_html( ffl_lang( 'Export fehlgeschlagen:', 'Export failed:' ) ); ?></strong> <?php echo esc_html( $error ); ?></p></div>
        <?php endif; ?>
        <?php if ( $ready ) : ?>
            <div class="notice notice-success"><p><strong><?php echo esc_html( ffl_lang( 'Datei wurde erfolgreich erstellt.', 'File created successfully.' ) ); ?></strong></p>
            <p><a class="button button-primary" href="<?php echo esc_url( $download_url ); ?>"><?php echo esc_html( sprintf( ffl_lang( '%s herunterladen', 'Download %s' ), $filename ) ); ?></a></p></div>
        <?php endif; ?>
        <form method="get">
            <input type="hidden" name="post_type" value="ffl_einsatz"><input type="hidden" name="page" value="ffl_einsatz_jahresbericht">
            <label><strong><?php echo esc_html( ffl_lang( 'Jahr', 'Year' ) ); ?>:</strong>
                <select name="year"><?php foreach ( $years as $available ) : ?><option value="<?php echo esc_attr( $available ); ?>" <?php selected( $year, $available ); ?>><?php echo esc_html( $available ); ?></option><?php endforeach; ?></select>
            </label> <?php submit_button( ffl_lang( 'Anzeigen', 'Show' ), 'secondary', 'submit', false ); ?>
        </form>
        <div class="card" style="max-width:900px;margin-top:20px">
            <h2><?php echo esc_html( sprintf( ffl_lang( 'Jahresbericht %d', 'Annual Report %d' ), $year ) ); ?></h2>
            <p><strong><?php echo esc_html( $stats['total'] ); ?></strong> <?php echo esc_html( ffl_lang( 'Einsätze', 'incidents' ) ); ?> · <strong><?php echo esc_html( floor( $stats['minutes'] / 60 ) . ':' . str_pad( (string) ( $stats['minutes'] % 60 ), 2, '0', STR_PAD_LEFT ) ); ?></strong> <?php echo esc_html( ffl_lang( 'Einsatzstunden', 'incident hours' ) ); ?></p>
            <div style="display:flex;gap:8px;flex-wrap:wrap;margin:16px 0">
                <?php foreach ( array( 'pdf' => 'PDF', 'csv' => 'CSV', 'xlsx' => 'XLSX' ) as $format => $label ) : ?>
                    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" style="margin:0">
                        <input type="hidden" name="action" value="ffl_generate_annual_report">
                        <input type="hidden" name="year" value="<?php echo esc_attr( $year ); ?>">
                        <input type="hidden" name="format" value="<?php echo esc_attr( $format ); ?>">
                        <?php wp_nonce_field( 'ffl_generate_annual_report_' . $year ); ?>
                        <button class="button button-primary" type="submit"><?php echo esc_html( sprintf( ffl_lang( '%s erstellen', 'Create %s' ), $label ) ); ?></button>
                    </form>
                <?php endforeach; ?>
                <a class="button" target="_blank" rel="noopener" href="<?php echo esc_url( wp_nonce_url( add_query_arg( array( 'action' => 'ffl_print_annual_report', 'year' => $year ), admin_url( 'admin-post.php' ) ), 'ffl_print_annual_report_' . $year ) ); ?>"><?php echo esc_html( ffl_lang( 'Browser-Druckansicht', 'Browser print view' ) ); ?></a>
            </div>
            <p class="description"><?php echo esc_html( ffl_lang( 'Nach dem Erstellen wird ein Download-Link angezeigt. Der Browser muss kein neues Fenster öffnen. Die Datei bleibt eine Stunde verfügbar.', 'After creation, a download link is displayed. No popup is required. The file remains available for one hour.' ) ); ?></p>
        </div>
    </div>
    <?php
}

add_action( 'admin_post_ffl_generate_annual_report', 'ffl_handle_annual_report_generation' );
function ffl_handle_annual_report_generation() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) ); }
    $year = absint( $_POST['year'] ?? wp_date( 'Y' ) );
    check_admin_referer( 'ffl_generate_annual_report_' . $year );
    $format = sanitize_key( $_POST['format'] ?? 'csv' );
    if ( ! in_array( $format, array( 'pdf', 'csv', 'xlsx' ), true ) ) { $format = 'csv'; }
    $rows = ffl_annual_report_rows( $year );
    if ( empty( $rows ) ) {
        wp_safe_redirect( ffl_annual_report_page_url( $year, array( 'ffl_report_error' => ffl_lang( 'Für dieses Jahr wurden keine veröffentlichten Einsätze gefunden.', 'No published incidents were found for this year.' ) ) ) ); exit;
    }
    $result = ffl_create_annual_report_file( $year, $format, $rows );
    if ( is_wp_error( $result ) ) {
        wp_safe_redirect( ffl_annual_report_page_url( $year, array( 'ffl_report_error' => $result->get_error_message() ) ) ); exit;
    }
    $token = strtolower( wp_generate_password( 24, false, false ) );
    set_transient( 'ffl_annual_report_' . get_current_user_id() . '_' . $token, $result, HOUR_IN_SECONDS );
    wp_safe_redirect( ffl_annual_report_page_url( $year, array( 'ffl_report_token' => $token ) ) ); exit;
}

add_action( 'admin_post_ffl_download_annual_report', 'ffl_handle_annual_report_download' );
function ffl_handle_annual_report_download() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) ); }
    $token = sanitize_key( $_GET['token'] ?? '' );
    check_admin_referer( 'ffl_download_annual_report_' . $token );
    $key = 'ffl_annual_report_' . get_current_user_id() . '_' . $token;
    $data = get_transient( $key );
    if ( ! is_array( $data ) || empty( $data['path'] ) || ! is_file( $data['path'] ) ) { wp_die( esc_html( ffl_lang( 'Die Exportdatei ist nicht mehr verfügbar. Bitte erneut erstellen.', 'The export file is no longer available. Please create it again.' ) ) ); }
    while ( ob_get_level() ) { ob_end_clean(); }
    nocache_headers();
    header( 'Content-Type: ' . $data['type'] );
    header( 'Content-Disposition: attachment; filename="' . sanitize_file_name( $data['name'] ) . '"' );
    header( 'Content-Length: ' . filesize( $data['path'] ) );
    readfile( $data['path'] );
    @unlink( $data['path'] ); delete_transient( $key ); exit;
}

add_action( 'admin_post_ffl_print_annual_report', 'ffl_handle_annual_report_print' );
function ffl_handle_annual_report_print() {
    if ( ! current_user_can( 'manage_options' ) ) { wp_die( esc_html( ffl_lang( 'Keine Berechtigung.', 'Permission denied.' ) ) ); }
    $year = absint( $_GET['year'] ?? wp_date( 'Y' ) );
    check_admin_referer( 'ffl_print_annual_report_' . $year );
    $rows = ffl_annual_report_rows( $year ); $stats = ffl_get_year_statistics( $year );
    ?><!doctype html><html lang="de"><head><meta charset="utf-8"><title><?php echo esc_html( 'Jahresbericht ' . $year ); ?></title><style>body{font-family:Arial,sans-serif;color:#222;margin:28px}h1{margin-bottom:4px}.summary{margin:14px 0 24px}table{border-collapse:collapse;width:100%;font-size:12px}th,td{border:1px solid #bbb;padding:6px;text-align:left;vertical-align:top}th{background:#eee}@media print{button{display:none}body{margin:10mm}}</style></head><body><button onclick="window.print()"><?php echo esc_html( ffl_lang( 'Drucken / als PDF speichern', 'Print / save as PDF' ) ); ?></button><h1><?php echo esc_html( get_option( 'ffl_organisation_name', get_bloginfo( 'name' ) ) ); ?></h1><h2><?php echo esc_html( 'Jahresbericht ' . $year ); ?></h2><div class="summary"><?php echo esc_html( $stats['total'] . ' ' . ffl_lang( 'Einsätze', 'incidents' ) ); ?> · <?php echo esc_html( floor( $stats['minutes']/60 ) . ':' . str_pad( (string) ( $stats['minutes']%60 ), 2, '0', STR_PAD_LEFT ) . ' ' . ffl_lang( 'Einsatzstunden', 'incident hours' ) ); ?></div><table><thead><tr><?php foreach ( ffl_annual_report_headers() as $head ) : ?><th><?php echo esc_html( $head ); ?></th><?php endforeach; ?></tr></thead><tbody><?php foreach ( $rows as $row ) : ?><tr><?php foreach ( ffl_annual_report_row_values( $row ) as $cell ) : ?><td><?php echo esc_html( $cell ); ?></td><?php endforeach; ?></tr><?php endforeach; ?></tbody></table></body></html><?php exit;
}

function ffl_annual_report_headers() { return array( 'Nr.', 'Datum', 'Uhrzeit', 'Einsatz', 'Einsatzart', 'Alarmstichwort', 'Ort', 'Dauer', 'Fahrzeuge' ); }
function ffl_annual_report_row_values( $row ) { return array_values( $row ); }

function ffl_create_annual_report_file( $year, $format, $rows ) {
    $uploads = wp_upload_dir();
    if ( ! empty( $uploads['error'] ) ) { return new WP_Error( 'uploads', $uploads['error'] ); }
    $dir = trailingslashit( $uploads['basedir'] ) . 'einsatzlyzer-exports';
    if ( ! wp_mkdir_p( $dir ) || ! is_writable( $dir ) ) { return new WP_Error( 'directory', ffl_lang( 'Der Exportordner konnte nicht erstellt oder beschrieben werden.', 'The export directory could not be created or written.' ) ); }
    if ( ! file_exists( $dir . '/index.php' ) ) { @file_put_contents( $dir . '/index.php', "<?php\n// Silence is golden.\n" ); }
    if ( ! file_exists( $dir . '/.htaccess' ) ) { @file_put_contents( $dir . '/.htaccess', "Require all denied\n" ); }
    $name = 'einsatzlyzer-jahresbericht-' . absint( $year ) . '-' . wp_date( 'Ymd-His' ) . '.' . $format;
    $path = trailingslashit( $dir ) . $name;
    if ( 'csv' === $format ) { $result = ffl_write_annual_report_csv( $path, $rows ); $type = 'text/csv; charset=UTF-8'; }
    elseif ( 'xlsx' === $format ) { $result = ffl_write_annual_report_xlsx( $path, $year, $rows ); $type = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'; }
    else { $result = ffl_write_annual_report_pdf( $path, $year, $rows ); $type = 'application/pdf'; }
    if ( is_wp_error( $result ) ) { return $result; }
    clearstatcache( true, $path );
    if ( ! is_file( $path ) || filesize( $path ) < 20 ) { return new WP_Error( 'empty', ffl_lang( 'Die Exportdatei wurde nicht korrekt erzeugt.', 'The export file was not created correctly.' ) ); }
    return array( 'path' => $path, 'name' => $name, 'type' => $type );
}

function ffl_write_annual_report_csv( $path, $rows ) {
    $out = @fopen( $path, 'wb' ); if ( ! $out ) { return new WP_Error( 'csv_open', ffl_lang( 'CSV-Datei konnte nicht geschrieben werden.', 'CSV file could not be written.' ) ); }
    fwrite( $out, "\xEF\xBB\xBF" ); fputcsv( $out, ffl_annual_report_headers(), ';' ); foreach ( $rows as $row ) { fputcsv( $out, ffl_annual_report_row_values( $row ), ';' ); } fclose( $out ); return true;
}

function ffl_xml_escape( $value ) { return htmlspecialchars( (string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8' ); }
function ffl_xlsx_column_name( $number ) { $name=''; while($number>0){$number--; $name=chr(65+($number%26)).$name; $number=intdiv($number,26);} return $name; }
function ffl_write_annual_report_xlsx( $path, $year, $rows ) {
    if ( ! class_exists( 'ZipArchive' ) ) { return new WP_Error( 'ziparchive', ffl_lang( 'XLSX kann nicht erstellt werden, weil die PHP-Erweiterung ZipArchive fehlt.', 'XLSX cannot be created because the PHP ZipArchive extension is missing.' ) ); }
    $stats=ffl_get_year_statistics($year); $table=array(array(get_option('ffl_organisation_name',get_bloginfo('name'))),array('Jahresbericht '.$year),array('Einsätze',$stats['total'],'Einsatzstunden',floor($stats['minutes']/60).':'.str_pad((string)($stats['minutes']%60),2,'0',STR_PAD_LEFT)),array(),ffl_annual_report_headers()); foreach($rows as $row){$table[]=ffl_annual_report_row_values($row);} $sheet=''; foreach($table as $r=>$cells){$sheet.='<row r="'.($r+1).'">'; foreach($cells as $i=>$cell){$ref=ffl_xlsx_column_name($i+1).($r+1); $sheet.='<c r="'.$ref.'" t="inlineStr"><is><t xml:space="preserve">'.ffl_xml_escape($cell).'</t></is></c>'; } $sheet.='</row>';}
    $files=array('[Content_Types].xml'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>','_rels/.rels'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>','xl/workbook.xml'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Jahresbericht '.$year.'" sheetId="1" r:id="rId1"/></sheets></workbook>','xl/_rels/workbook.xml.rels'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/><Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/></Relationships>','xl/styles.xml'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><fonts count="1"><font><sz val="11"/><name val="Calibri"/></font></fonts><fills count="1"><fill><patternFill patternType="none"/></fill></fills><borders count="1"><border/></borders><cellStyleXfs count="1"><xf/></cellStyleXfs><cellXfs count="1"><xf xfId="0"/></cellXfs></styleSheet>','xl/worksheets/sheet1.xml'=>'<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cols><col min="1" max="1" width="8" customWidth="1"/><col min="2" max="3" width="13" customWidth="1"/><col min="4" max="4" width="45" customWidth="1"/><col min="5" max="9" width="24" customWidth="1"/></cols><sheetData>'.$sheet.'</sheetData><autoFilter ref="A5:I'.count($table).'"/></worksheet>');
    $zip=new ZipArchive(); $opened=$zip->open($path,ZipArchive::CREATE|ZipArchive::OVERWRITE); if(true!==$opened){return new WP_Error('xlsx_open',ffl_lang('XLSX-Datei konnte nicht angelegt werden.','XLSX file could not be created.'));} foreach($files as $file=>$content){if(!$zip->addFromString($file,$content)){$zip->close();@unlink($path);return new WP_Error('xlsx_write',ffl_lang('XLSX-Inhalt konnte nicht geschrieben werden.','XLSX content could not be written.'));}} if(!$zip->close()){return new WP_Error('xlsx_close',ffl_lang('XLSX-Datei konnte nicht abgeschlossen werden.','XLSX file could not be finalized.'));} return true;
}

function ffl_pdf_text( $text ) { $text=wp_strip_all_tags(html_entity_decode((string)$text,ENT_QUOTES,'UTF-8')); $encoded=function_exists('iconv')?@iconv('UTF-8','Windows-1252//TRANSLIT',$text):$text; return str_replace(array('\\','(',')',"\r","\n"),array('\\\\','\\(','\\)',' ',' '),$encoded?:$text); }
function ffl_pdf_wrap( $text, $length=92 ) { return explode("\n",wordwrap(trim(preg_replace('/\s+/',' ',(string)$text)),$length,"\n",true)); }
function ffl_write_annual_report_pdf( $path, $year, $rows ) {
    $stats=ffl_get_year_statistics($year); $org=get_option('ffl_organisation_name',get_bloginfo('name')); $lines=array($org,'Jahresbericht '.$year,'','Einsätze: '.$stats['total'].'   Einsatzstunden: '.floor($stats['minutes']/60).':'.str_pad((string)($stats['minutes']%60),2,'0',STR_PAD_LEFT),''); foreach($stats['types'] as $type=>$count){$lines[]=$type.': '.$count;} $lines[]='';$lines[]='Einsatzliste';$lines[]=str_repeat('-',92); foreach($rows as $row){$base=sprintf('%s | %s %s | %s | %s | %s',$row['number'],$row['date'],$row['time'],$row['type'],$row['location'],$row['title']);foreach(ffl_pdf_wrap($base,92) as $line){$lines[]=$line;}$lines[]=str_repeat('-',92);} $pages=array_chunk($lines,48);$objects=array();$page_ids=array();$font_id=3;$next=4; foreach($pages as $page){$content="BT\n/F1 10 Tf\n45 800 Td\n14 TL\n";foreach($page as $line){$content.='('.ffl_pdf_text($line).") Tj\nT*\n";}$content.="ET";$content_id=$next++;$page_id=$next++;$objects[$content_id]='<< /Length '.strlen($content)." >>\nstream\n".$content."\nendstream";$objects[$page_id]='<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] /Resources << /Font << /F1 '.$font_id.' 0 R >> >> /Contents '.$content_id.' 0 R >>';$page_ids[]=$page_id;} $objects[1]='<< /Type /Catalog /Pages 2 0 R >>';$objects[2]='<< /Type /Pages /Kids ['.implode(' ',array_map(function($id){return $id.' 0 R';},$page_ids)).'] /Count '.count($page_ids).' >>';$objects[3]='<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica /Encoding /WinAnsiEncoding >>';ksort($objects);$pdf="%PDF-1.4\n";$offsets=array(0);foreach($objects as $id=>$obj){$offsets[$id]=strlen($pdf);$pdf.=$id." 0 obj\n".$obj."\nendobj\n";}$xref=strlen($pdf);$max=max(array_keys($objects));$pdf.="xref\n0 ".($max+1)."\n0000000000 65535 f \n";for($i=1;$i<=$max;$i++){$pdf.=sprintf('%010d 00000 n ',$offsets[$i]??0)."\n";}$pdf.="trailer\n<< /Size ".($max+1)." /Root 1 0 R >>\nstartxref\n$xref\n%%EOF"; if(false===@file_put_contents($path,$pdf)){return new WP_Error('pdf_write',ffl_lang('PDF-Datei konnte nicht geschrieben werden.','PDF file could not be written.'));} return true;
}
