# PHP-Router
Minimal router implementation for a php application

## Как использовать:

### Шаг первый:
    Заполнить массив Router::$GET как показано на примере ниже,
    своими URI/Controller/action/name/middleware

    Router::GET('/', \yourNamespace\SomeController::class, 'action')
           ->name('nameThisURI')
           ->middleware('short_name_your_middleware' или ['short_name_your_middleware', 'second_short_name_your_middleware']);
    
    Router::GET('/post/{post}/comment/{comment}', \PostsController::class, 'showComment')
           ->name('myNameForThisURI')
           ->middleware(['Authorization', 'Authentication']);
### Шаг второй:
    Заполнить массив Router::$middleware, как показано на примере ниже,
    для будущей замены у конкретного $routeConfig его коротких имён middleware на полные

    use Middleware;
    
    Router::middleware('short_name_your_middleware', yourNamespace\SomeMiddleware::class);
    Router::middleware('second_short_name_your_middleware', yourNamespace\SomeSecondMiddleware::class);
    Router::middleware('Authorization', Authorization::class);
    Router::middleware('Authentication', Authentication::class);    
### Шаг третий:
    $routeConfig = Router::compareURI($_SERVER['REQUEST_URI']);

Шаг №1 и шаг №2 можно поменять местами.
Главное, чтобы в момент вызова метода Router::compareURI($_SERVER['REQUEST_URI']) короткие имена middleware и им принадлежащие классы уже были добавлены в массив

### Дамп $routeConfig для uri '/post/123/comment/456':

    RouteConfig:
         'uri'          =>  '/post/{post}/comment/{comment}',
         'className'    =>  'PostsController',
         'action'       =>  'showComment',
         'uri_name'     =>  'commentPost',
         'middleware'   =>  [
                    0   =>  'Middleware\Authorization',
                    1   =>  'Middleware\Authentication',
                    ],
         'middlewareString' =>  false,
         'Comparisons'  =>  [
                     'post'     =>  '123',
                     'comment'  =>  '456',
                     ],
#### В поле 'Comparisons' нам доступны значения по именам, которые мы указали при назначении роута определённому uri:
    Router::GET('/myRoute/{intKey}/', \SomeController::class, 'action')