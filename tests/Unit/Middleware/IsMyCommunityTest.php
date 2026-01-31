<?php

declare(strict_types=1);

namespace Tests\Unit\Middleware;

use App\Http\Middleware\IsMyCommunity;
use App\Models\Community;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Tests\TestCase;

class IsMyCommunityTest extends TestCase
{
    use RefreshDatabase;

    protected IsMyCommunity $middleware;

    protected function setUp(): void
    {
        parent::setUp();
        $this->middleware = new IsMyCommunity();
    }

    public function test_allows_access_for_community_owner(): void
    {
        $user = User::factory()->create();
        $community = Community::factory()->create(['user_id' => $user->id]);

        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($community) {
            $route = $this->createMock(\Illuminate\Routing\Route::class);
            $route->method('parameter')->with('community')->willReturn($community);

            return $route;
        });

        $response = $this->middleware->handle($request, fn ($req) => response('OK'));

        $this->assertEquals('OK', $response->getContent());
    }

    public function test_denies_access_for_non_owner(): void
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $community = Community::factory()->create(['user_id' => $otherUser->id]);

        $request = Request::create('/test');
        $request->setUserResolver(fn () => $user);
        $request->setRouteResolver(function () use ($community) {
            $route = $this->createMock(\Illuminate\Routing\Route::class);
            $route->method('parameter')->with('community')->willReturn($community);

            return $route;
        });

        try {
            $this->middleware->handle($request, fn ($req) => response('OK'));
            $this->fail('Expected HttpException was not thrown');
        } catch (HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }
}
