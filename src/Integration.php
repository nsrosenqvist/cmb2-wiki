<?php namespace NSRosenqvist\CMB2\WikiField;

use DateTime;

class Integration
{
	static $init = false;

	static function init()
	{
		if (self::$init) {
			return;
		}

		$init = true;

        // Add assets
        add_action('admin_enqueue_scripts', function() {
			wp_register_style('cmb2_wiki', self::plugins_url('cmb2-wiki', '/assets/cmb2-wiki.css', __FILE__, 1), false, '1.0.0');
			wp_register_style('cmb2_wiki_loader', self::plugins_url('cmb2-wiki', '/assets/cmb2-wiki-loader.css', __FILE__, 1), false, '1.0.0');
			wp_register_script('cmb2_wiki', self::plugins_url('cmb2-wiki', '/assets/cmb2-wiki.js', __FILE__, 1), ['jquery'], '1.0.0');
            wp_enqueue_style('cmb2_wiki');
			wp_enqueue_style('cmb2_wiki_loader');
			wp_enqueue_script('cmb2_wiki');
        });

        // Renderer callback for switch
        add_action('cmb2_render_wiki', function($field, $escaped_value, $object_id, $object_type, $field_type_object) {
			$id = $field->_id();

			// Enable processing the files with PHP before display
			$preProcess = $field->args('pre_process') ?: false;

			// Wiki directory (used to create wiki links)
			$root = $field->args('wiki_root');
			$root = apply_filters('cmb2_wiki_'.$id.'_wiki_root', $root);

			// Wiki files
			$files = [];
			$loadFiles = $field->args('files');
			$loadFiles = apply_filters('cmb2_wiki_'.$id.'_files', $loadFiles);

			// Theme directory (used to display wiki file location in meta)
			$themePath = $field->args('theme_root') ?: null;
			$themePath = $themePath ?? get_stylesheet_directory();
			$themePath = apply_filters('cmb2_wiki_'.$id.'_theme_root', $themePath);

			// Load the contents of every file
			foreach ($loadFiles as $file) {
				if (! file_exists($file) && file_exists($root.'/'.$file)) {
					$file = $root.'/'.$file;
				}

				if (file_exists($file)) {
					$title = apply_filters('cmb2_wiki_file_title', null, $file);
					$title = apply_filters('cmb2_wiki_'.$id.'_file_title', $title, $file) ?? basename($file);

					// Either simply get the contents or include as PHP
					if (! $preProcess) {
						$content = file_get_contents($file);
					}
					else {
						$obLevel = ob_get_level();
				        ob_start();

				        try {
				            include $file;
				        }
						catch (Exception $e) {
				            // Do nothing
				        }

				        $content = ltrim(ob_get_clean());
					}

					$files[] = [
						'name' => str_replace(trailingslashit($root), '', $file),
						'title' => $title,
						'path' => $file,
						'root' => $root,
						'themePath' => str_replace(trailingslashit($themePath), '', $file),
						'relPath' => str_replace(trailingslashit($root), '', $file),
						'content' => $content,
					];
				}
			}

			echo '<div class="cmb2-wiki">';

			// Show files
			if (count($files) > 0) {
				$meta = $field->args('meta', false);
				$single = (count($files) == 1) ? true : false;

				if (! $single) {
					echo '<div class="cmb2-wiki-menu">';
					echo '<ul>';

					foreach ($files as $file) {
						echo '<li><a href="#'.$file['name'].'">'.$file['title'].'</a></li>';
					}

					echo '</ul>';
					echo '</div>';
				}

				echo '<div class="cmb2-wiki-files">';

				// Insert loader
				echo '<div class="cmb2-wiki-loader">';
				echo self::loaderHTML();
				echo '</div>';

				// Load all files
				foreach ($files as $index => $file) {
					$mtime = filemtime($file['path']);
					$mtime = DateTime::createFromFormat('U', $mtime);
					$previous = (isset($files[$index-1])) ? $files[$index-1] : null;
					$next = (isset($files[$index+1])) ? $files[$index+1] : null;

					echo '<div id="'.$file['name'].'" class="cmb2-wiki-file">';

					echo '<div class="cmb2-wiki-content">';
					$content = apply_filters('cmb2_wiki_file_content', $file['content'], $file['path'], $file['root']);
					$content = apply_filters('cmb2_wiki_'.$id.'_file_content', $content, $file['path'], $file['root']);
					echo $content;
					echo '</div>';

					if ($meta) {
						echo '<div class="cmb2-wiki-meta">';
						echo '<span>'.$file['themePath'].' (Modified: '.format_date($mtime).')</span>';
						echo '</div>';
					}

					if (! $single) {
						echo '<nav class="cmb2-wiki-nav">';
						echo ($previous) ? '<a class="cmb2-wiki-nav-previous" href="#'.$previous['name'].'">&lsaquo; '.$previous['title'].'</a>' : '';
						echo ($next) ? '<a class="cmb2-wiki-nav-next" href="#'.$next['name'].'">'.$next['title'].' &rsaquo;</a>' : '';
						echo '</nav>';
					}

					echo '</div>';
				}

				echo '</div>';
			}
			else {
				echo '<p>No files have been added to the wiki. Add files via the <code>cmb2_wiki_'.$id.'_files</code> filter or by adding them as an array to the field definition with the key <code>files</code>.</p>';
			}

			echo '</div>';
        }, 10, 5);
    }

	static function loaderHTML()
	{
		$html = '<div id="floatingCirclesG">';
		$html .= '	<div class="f_circleG" id="frotateG_01"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_02"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_03"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_04"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_05"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_06"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_07"></div>';
		$html .= '	<div class="f_circleG" id="frotateG_08"></div>';
		$html .= '</div>';
		return $html;
	}

	static function plugins_url($name, $file, $__FILE__, $depth = 0)
	{
		// Traverse up to root
		$dir = dirname($__FILE__);

		for ($i = 0; $i < $depth; $i++) {
			$dir = dirname($dir);
		}

		$root = $dir;
		$plugins = dirname($root);

		// Compare plugin directory with our found root
		if ($plugins !== WP_PLUGIN_DIR || $plugins !== WPMU_PLUGIN_DIR) {
			// Must be a symlink, guess location based on default directory name
			$resource = $name.'/'.$file;
			$url = false;

			if (file_exists(WPMU_PLUGIN_DIR.'/'.$resource)) {
				$url = WPMU_PLUGIN_URL.'/'.$resource;
			}
			elseif (file_exists(WP_PLUGIN_DIR.'/'.$resource)) {
				$url = WP_PLUGIN_URL.'/'.$resource;
			}

			if ($url) {
				if (is_ssl() && substr($url, 0, 7) !== 'https://') {
					$url = str_replace('http://', 'https://', $url);
				}

				return $url;
			}
		}

		return plugins_url($file, $root);
	}
}
