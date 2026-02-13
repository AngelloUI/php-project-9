<?php

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use DI\Container;
use GuzzleHttp\Client;
use Hexlet\Code\Repository\UrlChecksRepository;
use Hexlet\Code\Repository\UrlsRepository;
use Hexlet\Code\Service\UrlChecker;
use Slim\Factory\AppFactory;
use Slim\Flash\Messages;
use Slim\Views\Twig;
use Slim\Views\TwigMiddleware;
use Valitron\Validator;

session_start();

$container = new Container();
$container->set('flash', function () {
    return new Messages();
});

$app = AppFactory::create();

$twig = Twig::create(__DIR__ . '/../templates', [
        'cache' => false,
        'debug' => true]
);
$urlsRepository = new UrlsRepository();
$urlChecksRepository = new UrlChecksRepository();
$router = $app->getRouteCollector()->getRouteParser();
$client = new Client([
    'timeout' => 5,
    'http_errors' => false,
]);
$urlChecker = new UrlChecker($client);

$app->add(TwigMiddleware::create($app, $twig));

$errorMiddleware = $app->addErrorMiddleware(true, true, true);

$errorHandler = function (
    Psr\Http\Message\ServerRequestInterface $request,
    Throwable $exception,
    bool $displayErrorDetails,
    bool $logErrors,
    bool $logErrorDetails
) use ($twig) {
    $response = new Slim\Psr7\Response();
    return $twig->render($response->withStatus(500), 'error.html.twig');
};

$errorMiddleware->setDefaultErrorHandler($errorHandler);

$app->get('/welcome', function ($request, $response) use ($twig) {

    return $twig->render($response, 'welcome.html.twig');
})->setName('welcome');

$app->get('/', function ($request, $response, $args) use ($twig, $container) {
    $messages = $container->get('flash')->getMessages();

    return $twig->render($response, 'main.html.twig', ['flash' => $messages, 'urlName' => $messages['urlName'][0]]);
})->setName('main');

$app->get('/urls', function ($request, $response) use ($twig, $urlsRepository) {
    $urls = $urlsRepository->findAllWithLastCheck();

    return $twig->render($response, 'urls.html.twig', ['urls' => $urls]);
})->setName('urls.get');

$app->get('/urls/{id}', function ($request, $response, $args) use ($twig, $urlsRepository, $urlChecksRepository, $container) {
    $id = $args['id'];
    $url = $urlsRepository->findById($id);

    $checks = $urlChecksRepository->findAllChecksByUrlId($id);

    $messages = $container->get('flash')->getMessages();

    return $twig->render($response, 'url.html.twig', ['url' => $url, 'checks' => $checks, 'flash' => $messages]);
})->setName('url.get');

$app->post('/urls', function ($request, $response) use ($twig, $urlsRepository, $router, $container) {
    $inputUrl = (array) $request->getParsedBody();
    $inputUrl = $inputUrl['url']['name'] ?? '';
    $error = null;

    $validator = new Validator(['url' => $inputUrl]);
    $validator->rule('required', 'url')->message('URL не должен быть пустым');
    $validator->rule('url', 'url')->message('Некорректный URL');
    $validator->rule('lengthMax', 'url', 255)->message('URL слишком длинный');

    if (!$validator->validate()) {
        $error = $validator->errors()['url'][0];

        return $twig->render($response->withStatus(422), 'main.html.twig', [
            'flash' => [
                'error' => [$error]
            ],
            'urlName' => $inputUrl
        ]);
    }

    $url = parse_url($inputUrl);
    $url = $url['scheme'] . '://' . $url['host'];

    if ($data = $urlsRepository->findByName($url)){
        $container->get('flash')->addMessage('success', "Страница уже существует");
    } else {
        $data = $urlsRepository->save($url);
        $container->get('flash')->addMessage('success', "Страница успешно добавлена");
    }

    return $response
        ->withHeader('Location', $router->urlFor('url.get', ['id' => $data['id']]))
        ->withStatus(302);

})->setName('urls.post');

$app->post('/urls/{id}/checks', function ($request, $response, $args) use ($urlsRepository, $urlChecker, $urlChecksRepository, $router, $container, $twig) {
    $id = $args['id'];
    $urlName = $urlsRepository->findById($id)['name'];

    $checkResult = $urlChecker->check($urlName);
    $statusCode = $checkResult['status_code'];

    if (isset($statusCode)) {
        $id = (int)$id;
        $h1 = $checkResult['h1'];
        $title = $checkResult['title'];
        $description = $checkResult['description'];
        $urlChecksRepository->save($id, $statusCode, $h1, $title, $description);

        $container->get('flash')->addMessage('success', "Страница успешно проверена");
    } else {
        return $twig->render($response, 'error.html.twig');
    }

    return $response
        ->withHeader('Location', $router->urlFor('url.get', ['id' => $id]))
        ->withStatus(302);
})->setName('url.post.check');

$app->run();