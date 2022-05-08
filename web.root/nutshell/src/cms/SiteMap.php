<?php

namespace Nutshell\cms;

use Tops\sys\IUser;
use Tops\sys\TUser;

class SiteMap
{
    private $xmldata;
    /**
     * @var IUser
     */
    private $user;

    public function __construct($filePath=null)
    {
        if (!$filePath) {
            $filePath = DIR_CONFIG_SITE.'/sitemap.xml';
        }
        $this->xmldata = simplexml_load_file($filePath);
        if ($this->xmldata === false) {
            throw new \Exception("Data file not found: ".$filePath);
        }
        TUser::getCurrent();
    }

    public function getMenu($path='/*') {
        $n = $this->xmldata->xpath($path);
        $menu = [];
        foreach ($n[0] as $key => $node) {
            $item = new \stdClass();
            $item->name = $key;
            foreach ($node->attributes() as $name => $value) {
                $item->{$name} = sprintf('%s',$value);
            }
            if ($this->authorized($item->rolls ?? [])) {
                $menu[] = $item;
            }
        }
        return $menu;
    }

    private function authorized($roles)
    {
        if (empty($roles) || $this->user->isAdmin()) {
            return true;
        }
        $roles = explode(',',$roles);
        if ($this->user->isAuthenticated()) {
            foreach ($roles as $role) {
                if ($role=='authenticated' || $this->user->isMemberOf($role)) {
                    return true;
                }
            }
        }
        return false;
    }

    public function renderMenu($path,$activeItem) {
        $menu = $this->getMenu($path);
        foreach ($menu as $item) {
                // render item;
        }
    }

    public function renderTopNav($activePath) {
        $parts = explode('/',$activePath);
        $activeItem = array_pop($parts);
        $menu = $this->getMenu();
        foreach ($menu as $item) {

        }
    }

    public function renderBreadcrumbs($activePath) {
        $items = explode('/',$activePath);
        $crumbs = [];
        $path = '';
        foreach ($crumbs as $crumb) {
            if ($path) {
                $path .= '/';
            }
            $path .= $crumb;
            $menu = $this->getMenu($path);
            $crumbs[] = $menu[0];
        }
        foreach ($crumbs as $crumb) {
            // render crumb
        }
    }


}