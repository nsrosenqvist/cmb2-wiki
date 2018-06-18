<?php namespace NSRosenqvist\CMB2\WikiField\Markdown;

use InvalidArgumentException;
use League\CommonMark\HtmlElement;
use League\CommonMark\ElementRendererInterface;
use League\CommonMark\Util\Xml;
use League\CommonMark\Inline\Element\Link;
use League\CommonMark\Inline\Element\AbstractInline;
use League\CommonMark\Inline\Renderer\InlineRendererInterface;
use League\CommonMark\Inline\Renderer\LinkRenderer as BaseRenderer;

class LinkRenderer extends BaseRenderer
{
    protected $file;
    protected $root;

    public function __construct($file, $root) {
        $this->file = $file;
        $this->root = $root;
    }

    public function render(AbstractInline $inline, ElementRendererInterface $htmlRenderer)
    {
        if (! ($inline instanceof Link)) {
            throw new InvalidArgumentException('Incompatible inline type: ' . get_class($inline));
        }

        $url = Xml::escape($inline->getUrl(), true);

        // Check if the string is relative and isn't prepended by a hash yet
        if (strpos($url, '://') === false && substr($url, 0, 1) !== '#') {
            // Check if path is absolute (from root)
            if (substr($url, 0, 1) === '/') {
                $path = realpath($this->root.'/'.$url);
            }
            else {
                $path = realpath(dirname($this->file).'/'.$url);
            }

            if (file_exists($path)) {
                $relPath = str_replace(trailingslashit($this->root), '', $path);
                $inline->setUrl($relPath);
            }
        }

        return parent::render($inline, $htmlRenderer);
    }
}
