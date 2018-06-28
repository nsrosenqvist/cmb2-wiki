CMB2 Wiki Field
===============

Integrates a simple but powerful CMB2 field that is not meant to save user input but to display a wiki. Great to use for the landing page that explains the theme when using CMB2 to create a page for theme options.

```php
$docs = new_cmb2_box(array(
    'id'            => 'docs',
    'title'         => __('Documentation', 'theme'),
));

$wiki = $docs->add_field(array(
    'name' => __('Wiki', 'theme'),
    'id'   => 'wiki',
    'type' => 'wiki',
    'meta' => true, // Display file path and modification date
    'wiki_root' => __DIR__.'/wiki',
    'theme_root' => __DIR__,
    'pre_process' => true, // Enables running PHP code in the file before displaying it
    'files' => [
        __DIR__.'/wiki/Introduction.md',
    ],
));
```

Add a markdown renderer:

```php
use League\CommonMark\Converter;
use League\CommonMark\DocParser;
use League\CommonMark\Environment;
use League\CommonMark\HtmlRenderer;

use Webuni\CommonMark\TableExtension\TableExtension;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\Image;

// Prebuilt classes that fixes relative links within the files
// to be properly linked when displayed in the admin UI
use NSRosenqvist\CMB2\WikiField\Markdown\LinkRenderer;
use NSRosenqvist\CMB2\WikiField\Markdown\ImageRenderer;

// Configure Markdown renderer
add_filter('cmb2_wiki_file_content', function($content, $file, $root) { // cmb2_{field_id}_file_content
    $environment = Environment::createCommonMarkEnvironment();
    $environment->addExtension(new TableExtension());
    $environment->addInlineRenderer(Link::class, new LinkRenderer($file, $root));
    $environment->addInlineRenderer(Image::class, new ImageRenderer($file, $root));

    $converter = new Converter(new DocParser($environment), new HtmlRenderer($environment));
    return $converter->convertToHtml($content);
}, 10, 3);

// Configure markdown file title
add_filter('cmb2_wiki_file_title', function($name, $file) { // cmb2_{field_id}_file_title
    return basename($file, '.md');
}, 10, 2);
```
