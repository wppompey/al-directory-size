<?php
/**
 * Plugin Name:     AL Directory Size
 * Plugin URI: 		https://github.com/wppompey/al-directory-size
 * Description:     Displays disk usage and number of files for a WordPress installation
 * Version:         0.0.0
 * Author:          AndrewLeonard

 * License:         GPL-2.0-or-later
 * License URI:     https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:     uploads-bar-chart
 *
 * @package         uploads-bar-chart
 */

function al_directory_filter( $directory ) {
	// Will exclude everything under these directories
	$exclude = array('.git', 'vendor','node_modules');

	$filter = function ($file, $key, $iterator) use ($exclude) {
		if ($iterator->hasChildren() && !in_array($file->getFilename(), $exclude)) {
		return true;
		}
		return $file->isFile();
	};

	$innerIterator = new RecursiveDirectoryIterator(
		$directory,
	RecursiveDirectoryIterator::SKIP_DOTS
		);
	$iterator = new RecursiveIteratorIterator(
		new RecursiveCallbackFilterIterator($innerIterator, $filter)
	);
	foreach ($iterator as $pathname => $fileInfo) 	{
		$filename = basename($fileInfo);
		$folder=dirname($fileInfo).DIRECTORY_SEPARATOR;
		$array[]=$folder.'##$%'.$filename;

	}
	asort($array);
	return $array;

}

function al_directory_analysis( $array ) {
	$dir      ='';
	$dirsize  =0;
	$filecount=1;
	foreach ( $array as $item ) {
		$pieces=explode( "##$%", $item );
		if ( $dir == '' ) {
			$dir=$pieces[0];
		}
		if ( $pieces[0] == $dir ) {
			$dirsize=$dirsize + ( filesize( $pieces[0] . $pieces[1] ) );
			$filecount ++;
		} else {
			$array2[] =sprintf( '%10d', $dirsize ) . '#' . $filecount . '#' . $dir;
			$dir      =$pieces[0];
			$dirsize  =0;
			$filecount=1;
		}
	}
	rsort( $array2 );

	return $array2;
}

function al_directory_report( $array2, $root ) {
	$cutoff=0;
	$html  ='<table>';
	$html  .='<tr>';
	$html  .='<td>Directory<br>(root=' . $root . ')</td><td>Size (MBs)</td><td>Number<br>of files</td><td>Modified Date</td></tr>';
	foreach ( $array2 as $item ) {
		$pieces=explode( "#", $item );
		if ( $cutoff == 0 ) {
			$cutoff=$pieces[0] / 100;
		}
		$mb=$pieces[0] / 1024 / 1024;
		if ( $pieces[0] > $cutoff ) {
			$date=date( "j-m-y, H:i:s", filemtime( $pieces[2] ) );
			if ( $pieces[2] == ABSPATH ) {
				$dir='(root)';
			} else {
				$dir=substr( $pieces[2], strlen( ABSPATH ) );
			}
			$html.='<tr><td>' . $dir . '</td><td>' . number_format( $mb, 2 ) . '</td><td>' . $pieces[1] . '</td><td>' . $date . '</td></tr>';
		}
	}
	$html.='</tr>';
	$html.='</table>';
	return $html;
}
//Start added AL 17/10/2020
function al_directory_size(){
	$root  = ABSPATH ;//. '/wp-content/uploads';

	$array = al_directory_filter( $root );
	$array2 = al_directory_analysis( $array );
	$html = al_directory_report( $array2, $root );
	return $html;
}

add_shortcode( 'directory_size', 'al_directory_size');
//End added AL 17/10/2020
