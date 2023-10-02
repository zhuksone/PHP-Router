<?php

declare(strict_types=1);

namespace Zhuksone\Router;

use Exception;

interface InterfaceRouteConfig
{
    public static function new(string $uri, string $class_name, string $action, string $uri_name, string|array $middleware): static;
    
    public function middleware(string|array $middleware): void;
    
    public function name(string $uri_name): static;
}

interface InterfaceRouter
{
    public static function middleware(string $middleware_short_name, string $middleware_class_name): void;
    
    public static function GET(string $uri, string $class_name, string $action = 'index', string $uri_name = '', string|array $middleware = []);
    
    public static function POST();
    
    public static function compareURI(string $currentURI): InterfaceRouteConfig;
}

class RouteConfig implements InterfaceRouteConfig
{
    /**
     * @param string       $uri
     * @param string       $className
     * @param string       $action
     * @param string       $uri_name
     * @param string|array $middleware
     * @param bool         $middlewareString
     * @param string       $patternThisURI
     * @param array        $matches
     * @param array        $Comparisons
     */
    private function __construct
    (
        public string       $uri,
        public string       $className,
        public string       $action,
        public string       $uri_name,
        public string|array $middleware,
        public bool         $middlewareString,
        public string       $patternThisURI = '',
        public array        $matches = [],
        public array        $Comparisons = [],
    )
    {
        return $this;
    }
    
    /**
     * @param string       $uri
     * @param string       $class_name
     * @param string       $action
     * @param string       $uri_name
     * @param string|array $middleware
     *
     * @return static
     */
    public static function new(string $uri, string $class_name, string $action, string $uri_name, string|array $middleware): static
    {
        $patternThisURI = '/';
        $matches        = [1 => []];
        
        if ($uri !== '/')
        {
            if ($uri[-1] === '/')
            {
                $uri = rtrim($uri, '\/  \n\r\t\v\x00');
            }
            
            preg_match_all('/{([a-z]+)}/', $uri, $matches);
            
            $patternThisURI = preg_replace('/{([a-z]+)}/', '(\d+)', $uri);
        }
        
        return new static($uri, $class_name, $action, $uri_name, $middleware, is_string($middleware), '^' . $patternThisURI . '$', $matches[1]);
    }
    
    /**
     * @param string $uri_name
     *
     * @return $this
     */
    public function name(string $uri_name): static
    {
        $this->uri_name = $uri_name;
        return $this;
    }
    
    /**
     * @param string|array $middleware
     *
     * @return void
     */
    public function middleware(string|array $middleware): void
    {
        $this->middleware       = $middleware;
        $this->middlewareString = is_string($this->middleware);
    }
}

class Router implements InterfaceRouter
{
    /**
     * @var array<InterfaceRouteConfig> $GET
     * An array of objects that implement the 'InterfaceRouteConfig' interface.
     * Each object consists of private, read-only fields and methods that return information about its configuration.
     */
    private static array $GET = [];
    /**
     * @var array<InterfaceRouteConfig> $POST
     * An array of objects that implement the 'InterfaceRouteConfig' interface.
     * Each object consists of private, read-only fields and methods that return information about its configuration.
     */
    private static array $POST = [];
    
    /**
     * @var array<string, string> $middleware
     * An array containing the relationship of short names to full middleware names
     */
    private static array $middleware = [];
    
    private function __construct() {}
    
    /**
     * @param string       $uri
     * @param string       $class_name
     * @param string       $action
     * @param string       $uri_name
     * @param string|array $middleware
     *
     * @return InterfaceRouteConfig
     */
    public static function GET
    (
        string       $uri,
        string       $class_name,
        string       $action = 'index',
        string       $uri_name = '',
        string|array $middleware = [],
    ): InterfaceRouteConfig
    {
        $routeConfig = RouteConfig::new($uri, $class_name, $action, $uri_name, $middleware);
        
        self::$GET[] = $routeConfig;
        
        return $routeConfig;
    }
    
    public static function POST() {}
    
    /**
     * @param string $currentURI
     *
     * @return InterfaceRouteConfig
     * @throws Exception
     */
    public static function compareURI(string $currentURI): InterfaceRouteConfig
    {
        foreach (self::$GET as $routeConfig)
        {
            if (preg_match('#' . $routeConfig->patternThisURI . '#', $currentURI, $matches))
            {
                foreach ($routeConfig->matches as $matchesKey => $matchesValue)
                {
                    $routeConfig->Comparisons[$matchesValue] = $matches[$matchesKey + 1];
                }
                
                if ($routeConfig->middlewareString)
                {
                    $routeConfig->middleware = self::$middleware[$routeConfig->middleware];
                } else
                {
                    foreach ($routeConfig->middleware as $mw_key => $mw_short_name)
                    {
                        $routeConfig->middleware[$mw_key] = self::$middleware[$mw_short_name];
                    }
                }
                
                unset($routeConfig->matches, $routeConfig->patternThisURI);
                
                return $routeConfig;
            }
        }
        
        throw new Exception('No suitable route found');
    }
    
    /**
     * @param string $middleware_short_name
     * @param string $middleware_class_name
     *
     * @return void
     */
    public static function middleware(string $middleware_short_name, string $middleware_class_name): void
    {
        self::$middleware[$middleware_short_name] = $middleware_class_name;
    }
}