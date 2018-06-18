<?php namespace NSRosenqvist\CMB2\WikiField\Providers;

use NSRosenqvist\CMB2\WikiField\Integration;

use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;

use Webuni\CommonMark\TableExtension\TableExtension;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Image;
use NSRosenqvist\CMB2\WikiField\Markdown\LinkRenderer;
use NSRosenqvist\CMB2\WikiField\Markdown\ImageRenderer;

class DynamisProvider extends \Dynamis\ServiceProvider
{
    function boot()
    {
        // Add integration
        Integration::init();

        // Remove plugin from WP Plugins list if we enable it through a provider
        add_filter('all_plugins', function($plugins) {
            foreach ($plugins as $key => $details) {
                if ($details['Name'] == 'CMB2 Wiki') {
                    unset($plugins[$key]);
                    break;
                }
            }

            return $plugins;
        }, 10, 1);

        // Configure Markdown renderer
        add_filter('cmb2_wiki_file_content', function($content, $file, $root) {
            $environment = Environment::createCommonMarkEnvironment();
            $environment->addExtension(new TableExtension());
            $environment->addInlineRenderer(Link::class, new LinkRenderer($file, $root));
            $environment->addInlineRenderer(Image::class, new ImageRenderer($file, $root));

            $converter = new Converter(new DocParser($environment), new HtmlRenderer($environment));
            return $converter->convertToHtml($content);
        }, 10, 3);

        // Configure markdown file title
        add_filter('cmb2_wiki_file_title', function($name, $file) {
            return basename($file, '.md');
        }, 10, 2);
    }
}
