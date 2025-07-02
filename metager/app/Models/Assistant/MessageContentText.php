<?php

namespace App\Models\Assistant;

use App\Http\Controllers\Pictureproxy;
use Arr;
use DOMDocument;
use League\CommonMark\Util\HtmlFilter;
use Str;

class MessageContentText extends MessageContent
{
    public readonly string $message;
    private bool $finished;

    public function __construct(string $message, $finished = true)
    {
        $this->message = $message;
        $this->finished = $finished;
    }

    public function setFinished(bool $finished): void
    {
        // Set whether the message is finished
        $this->finished = $finished;
    }

    public function append(string $text): void
    {
        // Append text to the existing message
        $this->message .= $text;
    }

    public function render(MessageRole $role = MessageRole::User): string
    {
        if ($role === MessageRole::User) {
            // Escape HTML for user messages
            return HtmlFilter::filter($this->message, HtmlFilter::ESCAPE);
        } elseif ($role === MessageRole::Agent) {
            // Allow HTML for assistant messages and parse markdown
            $html = Str::of($this->message)->markdown([
                "html_input" => HtmlFilter::ESCAPE,
            ])->toString();
            if (!empty($html)) {
                $html_dom = new DOMDocument(encoding: 'UTF-8');
                $html_dom->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', "UTF-8"), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD | LIBXML_NOERROR);

                foreach ($html_dom->getElementsByTagName('a') as $a) {
                    // Remove target attribute from links
                    $a->setAttribute('target', '_blank');
                    $a->setAttribute('rel', 'noopener');

                    $href = $a->getAttribute('href');
                    $components = parse_url($href);

                    if ($components !== false) {
                        $query_params = [];
                        parse_str(Arr::get($components, 'query', ''), $query_params);
                        unset($query_params['utm_source']);
                        $a->setAttribute('href', $components['scheme'] . '://' . $components['host'] . (isset($components['path']) ? $components['path'] : '') . (!empty($query_params) ? '?' . http_build_query($query_params) : ''));
                    }
                }

                foreach ($html_dom->getElementsByTagName('img') as $img) {
                    // Set images to only display if the message is finished
                    if (!$this->finished) {
                        $img->setAttribute("src", "");
                    } else {
                        $img_src = $img->getAttribute("src");
                        $img->setAttribute("src", Pictureproxy::generateUrl($img_src));
                    }
                }

                return $html_dom->saveHTML();
            }
            return $html;
        } else {
            return "";
        }
    }

    public function serialize(): string|null
    {
        return serialize([
            $this->message
        ]);
    }

    public function unserialize(string $data): void
    {
        list($this->message) = unserialize($data);
    }
}