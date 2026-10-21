<?php

namespace Tests\Feature;

use Illuminate\Routing\Route as RoutingRoute;
use Illuminate\Support\Facades\Route;
use ReflectionMethod;
use ReflectionNamedType;
use Tests\TestCase;

/**
 * 临时冒烟测试：以每个内置角色真实登录，遍历所有 GET 页面，检查是否出现 500 / 异常。
 * 验证完成后请删除本文件。
 */
class SmokeTest extends TestCase
{
    private array $lines = [];

    private array $users = [
        'admin' => 'admin@admin.com',
        'sales_manager' => 'sales@demo.com',
        'purchase_manager' => 'purchase@demo.com',
        'production_manager' => 'production@demo.com',
        'warehouse_manager' => 'warehouse@demo.com',
        'quality_manager' => 'quality@demo.com',
        'accountant' => 'accounting@demo.com',
        'viewer' => 'viewer@demo.com',
    ];

    public function test_crawl_all_pages_for_every_role(): void
    {
        $routes = $this->collectRoutes();

        foreach ($this->users as $role => $email) {
            $this->resetAuth();
            $ok = $this->loginAs($email);
            if (!$ok) {
                $this->lines[] = "{$role} | LOGIN FAILED | {$email}";
                file_put_contents(storage_path('logs/smoke-report.txt'), implode(PHP_EOL, $this->lines));
                continue;
            }

            foreach ($routes as $item) {
                [$uri, $name] = $item;
                $result = $this->visit($uri);
                $this->lines[] = "{$role} | {$result} | {$name} | {$uri}";
            }

            file_put_contents(storage_path('logs/smoke-report.txt'), implode(PHP_EOL, $this->lines));
        }

        $this->assertTrue(true);
    }

    private function resetAuth(): void
    {
        \Illuminate\Support\Facades\Auth::guard('web')->logout();
        \Illuminate\Support\Facades\Session::flush();
        app('session')->flush();
    }

    private function loginAs(string $email): bool
    {
        foreach (['123456', 'password'] as $pwd) {
            $r = $this->post('/login', ['email' => $email, 'password' => $pwd]);
            if ($r->isRedirect() && !str_contains($r->headers->get('Location') ?? '', '/login')) {
                return true;
            }
            if (\Illuminate\Support\Facades\Auth::check()) {
                return true;
            }
        }

        return false;
    }

    private function visit(string $uri): string
    {
        $this->withoutExceptionHandling();
        try {
            $response = $this->get($uri);
            $code = $response->getStatusCode();
            if ($code >= 500) {
                return (string) $code;
            }
            return (string) $code;
        } catch (\Symfony\Component\HttpKernel\Exception\NotFoundHttpException $e) {
            return '404';
        } catch (\Throwable $e) {
            return 'EXC:' . get_class($e) . ':' . substr($e->getMessage(), 0, 180);
        } finally {
            $this->withExceptionHandling();
        }
    }

    /**
     * @return array<int, array{0:string,1:string}>
     */
    private function collectRoutes(): array
    {
        $out = [];
        foreach (Route::getRoutes() as $route) {
            /** @var RoutingRoute $route */
            if (!in_array('GET', $route->methods(), true)) {
                continue;
            }
            $uri = $route->uri();
            if ($uri === '/' || str_starts_with($uri, '_')) {
                if ($uri === '/') {
                    $out[] = ['/', 'home'];
                }
                continue;
            }
            if (in_array($uri, ['login', 'register'], true)) {
                continue;
            }
            $out[] = [$this->buildUri($route), $route->getName() ?: $uri];
        }

        return $out;
    }

    private function buildUri(RoutingRoute $route): string
    {
        $uri = $route->uri();
        $values = [];

        $action = $route->getAction();
        $controller = $action['controller'] ?? null;
        if (is_string($controller) && str_contains($controller, '@')) {
            [$class, $method] = explode('@', $controller);
            if (class_exists($class) && method_exists($class, $method)) {
                $rm = new ReflectionMethod($class, $method);
                foreach ($rm->getParameters() as $p) {
                    $type = $p->getType();
                    if ($type instanceof ReflectionNamedType && !$type->isBuiltin()) {
                        $cls = $type->getName();
                        if (is_subclass_of($cls, \Illuminate\Database\Eloquent\Model::class)) {
                            $record = $cls::query()->first();
                            if ($record) {
                                $values[$p->getName()] = $record->getRouteKey();
                            }
                        }
                    }
                }
            }
        }

        $replacements = [];
        preg_match_all('/\{([a-zA-Z_]+?)(\?)?\}/', $uri, $m);
        foreach ($m[0] ?? [] as $i => $token) {
            $name = $m[1][$i];
            $replacements[$token] = $values[$name] ?? '1';
        }

        $uri = str_replace(array_keys($replacements), array_values($replacements), $uri);

        return '/' . ltrim($uri, '/');
    }
}
