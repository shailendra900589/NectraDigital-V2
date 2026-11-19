<?php
namespace Growth\Engines;

use Growth\Models\Service;
use Growth\Models\City;
use Growth\Models\Industry;
use Growth\Models\Tool;

class InternalLinkEngine
{
    public static function generate(array $service, array $city, array $ctx, ?array $industry = null): array
    {
        $links = [];
        $links[] = ['url' => '/', 'title' => 'Home', 'type' => 'core'];
        $links[] = ['url' => '/services', 'title' => 'All Services', 'type' => 'core'];
        $links[] = ['url' => '/contact?city=' . urlencode($city['name']), 'title' => 'Contact Us', 'type' => 'cta'];
        $links[] = ['url' => '/about', 'title' => 'About Nectra Digital', 'type' => 'core'];
        $links[] = ['url' => '/insights', 'title' => 'Blog & Intel', 'type' => 'blog'];
        $links[] = ['url' => '/aeo', 'title' => 'SEO Answers', 'type' => 'core'];
        $links[] = ['url' => '/tools', 'title' => 'Free SEO Tools', 'type' => 'tools'];
        $links[] = ['url' => '/editorial-guidelines', 'title' => 'Editorial Policy', 'type' => 'eeat'];

        $services = Service::all(true);
        $count = 0;
        foreach ($services as $s) {
            if ((int)$s['id'] === (int)$service['id']) continue;
            if ($count >= 3) break;
            $slug = ge_build_landing_slug($s['url_prefix'], $city['slug']);
            $links[] = ['url' => '/' . $slug, 'title' => $s['name'] . ' in ' . $city['name'], 'type' => 'service'];
            $count++;
        }

        $cities = City::all(true);
        $cityCount = 0;
        foreach ($cities as $c) {
            if ((int)$c['id'] === (int)$city['id']) continue;
            if ($cityCount >= 3) break;
            $slug = ge_build_landing_slug($service['url_prefix'], $c['slug']);
            $links[] = ['url' => '/' . $slug, 'title' => $service['name'] . ' in ' . $c['name'], 'type' => 'city'];
            $cityCount++;
        }

        if (ge_table_exists('ge_industries')) {
            $industries = Industry::all(true);
            $indCount = 0;
            foreach ($industries as $ind) {
                if ($industry && (int)$ind['id'] === (int)$industry['id']) continue;
                if ($indCount >= 2) break;
                $slug = ge_build_landing_slug($service['url_prefix'], $city['slug'], ['industry_slug' => $ind['slug']]);
                $links[] = ['url' => '/' . $slug, 'title' => $service['name'] . ' for ' . $ind['name'] . ' in ' . $city['name'], 'type' => 'industry'];
                $indCount++;
            }
        }

        if (ge_table_exists('ge_tools')) {
            foreach (array_slice(Tool::all(true), 0, 2) as $tool) {
                $links[] = ['url' => '/tools/' . $tool['slug'], 'title' => $tool['name'], 'type' => 'tools'];
            }
        }

        $links[] = ['url' => '/contact?service=' . urlencode('Software Development'), 'title' => 'Free Software Discovery Call', 'type' => 'cta'];
        $links[] = ['url' => '/contact?service=' . urlencode('Consultation'), 'title' => 'Schedule Strategy Call', 'type' => 'cta'];
        $links[] = ['url' => '/contact?service=' . urlencode('Request Proposal'), 'title' => 'Request Proposal', 'type' => 'cta'];

        return $links;
    }
}
