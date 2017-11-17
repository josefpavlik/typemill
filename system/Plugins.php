<?php

namespace Typemill;

class Plugins
{	
	public function load()
	{
		$pluginFolder = $this->scanPluginFolder();
		$classNames = array();

		/* iterate over plugin folders */
		foreach($pluginFolder as $plugin)
		{

			$className = DIRECTORY_SEPARATOR . 'Plugins' . DIRECTORY_SEPARATOR . $plugin . DIRECTORY_SEPARATOR . $plugin;
						
			/* if plugin-class and subscribe-method exists, add classname to array */			
			if(class_exists($className) /* && method_exists($className, 'getSubscribedEvents') */)
			{
				$classNames[] = $className;				
			}
		}
		return $classNames;
	}
	
	public function getNewRoutes($className, $routes)
	{
		
		/* if route-method exists in plugin-class */
		if(method_exists($className, 'addNewRoutes'))
		{
			/* add the routes */
			$pluginRoutes = $className::addNewRoutes();
			
			/* multi-dimensional or simple array of routes */
			if(isset($pluginRoutes[0]))
			{
				/* if they are properly formatted, add them to routes array */
				foreach($pluginRoutes as $pluginRoute)
				{
					if($this->checkRouteArray($pluginRoute))
					{
						$routes[] = $pluginRoute;
					}
				}
			}
			elseif(is_array($routes))
			{
				if($this->checkRouteArray($pluginRoutes))
				{
					$routes[] = $pluginRoutes;
				}
			}
		}
		
		return $routes;
	}
	
	public function getNewMiddleware($className)
	{
		if(method_exists($className, 'addNewMiddleware'))
		{
			/* check array */
			return $className::addNewMiddleware();
		}
	}
	
	private function checkRouteArray($route)
	{
		if( 
			isset($route['httpMethod']) AND in_array($route['httpMethod'], array('get','post','put','delete','head','patch','options'))
			AND isset($route['route']) AND is_string($route['route'])
			AND isset($route['class']) AND is_string($route['class']))
		{
			return true;
		}
		return false;
	}
	
	private function scanPluginFolder()
	{
		$pluginsDir = __DIR__ . '/../plugins';
		
		/* check if plugins directory exists */
		if(!is_dir($pluginsDir)){ return array(); }
		
		/* get all plugins folder */
		$plugins = array_diff(scandir($pluginsDir), array('..', '.'));
		
		return $plugins;
	}
}