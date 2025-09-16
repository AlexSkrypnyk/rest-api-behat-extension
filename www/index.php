<?php

/**
 * Simple test server for Behat tests - replaced Silex with plain PHP routing.
 */

require_once __DIR__.'/../vendor/autoload.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

$request = Request::createFromGlobals();
$path = trim($request->getPathInfo(), '/');

// Set JSON content type header
header('Content-Type: application/json');

switch ($path) {
    case 'echo':
        $ret = array(
            'warning' => 'Do not expose this service in production : it is intrinsically unsafe',
        );

        $ret['method'] = $request->getMethod();

        // Forms should be read from request, other data straight from input.
        $requestData = $request->request->all();
        if (!empty($requestData)) {
            foreach ($requestData as $key => $value) {
                $ret[$key] = $value;
            }
        }

        /** @var string $content */
        $content = $request->getContent(false);
        if (!empty($content)) {
            $data = json_decode($content, true);
            if (!is_array($data)) {
                $ret['content'] = $content;
            } else {
                foreach ($data as $key => $value) {
                    $ret[$key] = $value;
                }
            }
        }

        $ret['headers'] = array();
        foreach ($request->headers->all() as $k => $v) {
            $ret['headers'][$k] = $v;
        }
        foreach ($request->query->all() as $k => $v) {
            $ret['query'][$k] = $v;
        }

        echo json_encode($ret);
        break;

    case 'error_random':
        $statusCode = time() % 3 <= 0 ? 200 : 502;
        http_response_code($statusCode);
        echo json_encode([]);
        break;

    case 'always_error':
        http_response_code(502);
        echo json_encode([]);
        break;

    case 'post-html-form':
        echo json_encode([
            'content_type_header_value' => $request->headers->get('content-type'),
            'post_fields_count' => $request->request->count(),
            'post_fields' => $request->request->all(),
        ]);
        break;

    case 'post-html-form-with-files':
        echo json_encode([
            'content_type_header_value' => $request->headers->get('content-type'),
            'post_files_count' => count($request->files),
            'post_fields_count' => $request->request->count(),
            'post_fields' => $request->request->all(),
        ]);
        break;

    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not found']);
        break;
}