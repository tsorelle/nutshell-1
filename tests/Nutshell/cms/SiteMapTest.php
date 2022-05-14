<?php

namespace Nutshell\cms;

use PHPUnit\Framework\TestCase;
use Tops\sys\TParseDown;
use Tops\sys\TPath;

class SiteMapTest extends TestCase
{

    public function testGetMenu()
    {

        $map = new SiteMap(DIR_TEST_DATA.'/test-sitemap.xml');
        $actual = $map->getMenu();
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('songs');
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('songs/cowboy');
        $this->assertNotEmpty($actual);

    }

    public function testGetSiteMenu()
    {

        // $map = new SiteMap(DIR_TEST_DATA.'/test-sitemap.xml');
        $map = new SiteMap();
        $actual = $map->getMenu();
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('about');
        $this->assertNotEmpty($actual);

        $actual = $map->getMenu('about/nutshell');
        $this->assertNotEmpty($actual);

    }

    public function testRenderTopNav()
    {

    }

    public function testRenderMenu()
    {

    }

    public function testRenderBreadcrumbs()
    {

    }
}
