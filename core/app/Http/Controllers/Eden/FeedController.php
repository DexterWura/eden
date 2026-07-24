<?php

namespace App\Http\Controllers\Eden;

use App\Http\Controllers\Controller;
use App\Models\Startup;
use Illuminate\Http\Response;

class FeedController extends Controller
{
    public function new(): Response
    {
        $startups = Startup::query()
            ->where('status', Startup::STATUS_ACTIVE)
            ->with('activeFundingRound')
            ->orderByDesc('created_at')
            ->limit(50)
            ->get();

        return $this->rssResponse(
            'New apps on ' . $this->siteName(),
            'Latest apps added to the directory.',
            url('/'),
            $startups,
            'new'
        );
    }

    public function featured(): Response
    {
        $startups = Startup::query()
            ->where('status', Startup::STATUS_ACTIVE)
            ->where('is_featured', true)
            ->with('activeFundingRound')
            ->orderByDesc('upvotes')
            ->limit(50)
            ->get();

        return $this->rssResponse(
            'Featured apps on ' . $this->siteName(),
            'Hand-picked featured startups.',
            url('/?featured=1'),
            $startups,
            'featured'
        );
    }

    private function siteName(): string
    {
        return function_exists('gs') && gs('site_name') ? (string) gs('site_name') : 'Eden';
    }

    private function rssResponse(string $title, string $description, string $link, $startups, string $slug): Response
    {
        $siteName = $this->siteName();
        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">' . "\n";
        $xml .= '  <channel>' . "\n";
        $xml .= '    <title>' . $this->escapeXml($title) . '</title>' . "\n";
        $xml .= '    <description>' . $this->escapeXml($description) . '</description>' . "\n";
        $xml .= '    <link>' . $this->escapeXml($link) . '</link>' . "\n";
        $xml .= '    <atom:link href="' . $this->escapeXml(url('/feed/' . $slug)) . '" rel="self" type="application/rss+xml"/>' . "\n";
        $xml .= '    <lastBuildDate>' . gmdate('r') . '</lastBuildDate>' . "\n";

        foreach ($startups as $startup) {
            $itemUrl = url('/startup/' . $startup->slug);
            $itemTitle = $startup->name;
            $itemDesc = $startup->short_description ?? '';
            $pubDate = $startup->created_at ? $startup->created_at->format('r') : gmdate('r');
            $xml .= '    <item>' . "\n";
            $xml .= '      <title>' . $this->escapeXml($itemTitle) . '</title>' . "\n";
            $xml .= '      <description>' . $this->escapeXml($itemDesc) . '</description>' . "\n";
            $xml .= '      <link>' . $this->escapeXml($itemUrl) . '</link>' . "\n";
            $xml .= '      <guid isPermaLink="true">' . $this->escapeXml($itemUrl) . '</guid>' . "\n";
            $xml .= '      <pubDate>' . $pubDate . '</pubDate>' . "\n";
            $xml .= '    </item>' . "\n";
        }

        $xml .= '  </channel>' . "\n";
        $xml .= '</rss>';

        return response($xml, 200, [
            'Content-Type' => 'application/rss+xml; charset=UTF-8',
            'Cache-Control' => 'public, max-age=300',
        ]);
    }

    private function escapeXml(string $s): string
    {
        return htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}
