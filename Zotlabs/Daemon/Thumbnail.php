<?php /** @file */

namespace Zotlabs\Daemon;


class Thumbnail {

	static public function run($argc,$argv) {

		if(! $argc == 2)
			return;

		$c = q("select * from attach where hash = '%s' ",
			dbesc($argv[1])
		);

		if(! $c)
			return;

		$preview_style   = intval(get_config('system','thumbnail_security',0));
		$preview_width   = intval(get_config('system','thumbnail_width',300));
		$preview_height  = intval(get_config('system','thumbnail_height',300));

		$attach = $c[0];
		$default_controller = null;
		
		$files = glob('Zotlabs/Thumbs/*.php');
		if($files) {
			foreach($files as $f) {
				$clsname = '\\Zotlabs\\Thumbs\\' . ucfirst(basename($f,'.php'));
				if(class_exists($clsname)) {
					$x = new $clsname();
					if(method_exists($x,'Match')) {
						$matched = $x->Match($attach['filetype']);
						if($matched) {
							$x->Thumb($attach,$preview_style,$preview_width,$preview_height);
						}
					}
					if(method_exists($x,'MatchDefault')) {
						$default_matched = $x->MatchDefault(substr($attach['filetype'],0,strpos($attach['filetype'],'/')));
						if($default_matched) {
							$default_controller = $x;
						}
					}
				}
			}
		}
		if(($default_controller) && (! file_exists(dbunescbin($attach['content']) . '.thumb'))) {
			$default_controller->Thumb($attach,$preview_style,$preview_width,$preview_height);
		}
	}
}
