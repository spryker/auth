<?php

/**
 * Copyright © 2016-present Spryker Systems GmbH. All rights reserved.
 * Use of this software requires acceptance of the Evaluation License Agreement. See LICENSE file.
 */

namespace SprykerTest\Zed\Auth\Communication\Plugin\ServiceProvider;

use Codeception\Test\Unit;
use Spryker\Zed\Auth\AuthConfig;
use Spryker\Zed\Auth\Communication\Plugin\ServiceProvider\RedirectAfterLoginProvider;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * @deprecated When the deprecated plugin gets removed, remove this test as well.
 *
 * Auto-generated group annotations
 *
 * @group SprykerTest
 * @group Zed
 * @group Auth
 * @group Communication
 * @group Plugin
 * @group ServiceProvider
 * @group RedirectAfterLoginProviderTest
 * Add your own group annotations below this line
 */
class RedirectAfterLoginProviderTest extends Unit
{
    public const REQUEST_URI = 'REQUEST_URI';
    public const REDIRECT_URL_VALID = '/valid-redirect-url?query=string';
    public const REDIRECT_URL_INVALID = 'http://foo/redirect-url?query=string';
    public const REDIRECT_URL_INVALID_WITHOUT_HTTP = '/\example.com:8080/reidrect-url?query=string';

    /**
     * @return void
     */
    public function setUp(): void
    {
        Request::setTrustedHosts([]);
        Request::setTrustedProxies([], Request::HEADER_X_FORWARDED_ALL);
    }

    /**
     * @return void
     */
    public function testOnKernelResponseShouldSetRefererWhenRedirectingToLogin(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();

        $request->server->set(static::REQUEST_URI, static::REDIRECT_URL_VALID);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_LOGIN);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->never())
            ->method('isAuthenticated');
        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame('/auth/login?referer=%2Fvalid-redirect-url%3Fquery%3Dstring', $event->getResponse()->headers->get('location'));
    }

    /**
     * @return void
     */
    public function testOnKernelResponseShouldNotSetInvalidRefererWhenRedirectingToLogin(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->setMethod('POST');
        $request->server->set(static::REQUEST_URI, static::REDIRECT_URL_VALID);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_LOGIN);

        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->never())
            ->method('isAuthenticated');
        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame('/auth/login', $event->getResponse()->headers->get('location'));
    }

    /**
     * @return void
     */
    public function testOnKernelResponseShouldNotChangeResponseIfRedirectUriNotSetInReferer(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->server->set(static::REQUEST_URI, AuthConfig::DEFAULT_URL_LOGIN);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_REDIRECT);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @return void
     */
    public function testOnKernelResponseShouldNotUseInvalidReferer(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->server->set(static::REQUEST_URI, AuthConfig::DEFAULT_URL_LOGIN);
        $request->query->set(RedirectAfterLoginProvider::REFERER, static::REDIRECT_URL_INVALID);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_REDIRECT);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame('/', $event->getResponse()->headers->get('location'));
    }

    /**
     * @return void
     */
    public function testOnKernelResponseShouldNotUseInvalidRefererWithoutHttp(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->server->set(static::REQUEST_URI, AuthConfig::DEFAULT_URL_LOGIN);
        $request->query->set(RedirectAfterLoginProvider::REFERER, static::REDIRECT_URL_INVALID_WITHOUT_HTTP);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_REDIRECT);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame('/', $event->getResponse()->headers->get('location'));
    }

    /**
     * @return void
     */
    public function testOnKernelResponseMustNotSetRedirectUriIfRedirectUriSetAndUserIsNotAuthenticated(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->server->set(static::REQUEST_URI, AuthConfig::DEFAULT_URL_LOGIN);
        $request->query->set(RedirectAfterLoginProvider::REFERER, static::REDIRECT_URL_VALID);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_REDIRECT);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(false);

        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertSame($response, $event->getResponse());
    }

    /**
     * @return void
     */
    public function testOnKernelResponseMustSetRedirectResponseIfRedirectUriSetInRefererAndUserIsAuthenticated(): void
    {
        $kernel = $this->getHttpKernel();
        $request = new Request();
        $request->server->set(static::REQUEST_URI, AuthConfig::DEFAULT_URL_LOGIN);
        $request->query->set(RedirectAfterLoginProvider::REFERER, static::REDIRECT_URL_VALID);
        $response = new RedirectResponse(AuthConfig::DEFAULT_URL_REDIRECT);
        $event = new ResponseEvent($kernel, $request, HttpKernelInterface::MASTER_REQUEST, $response);

        $redirectAfterLoginProvider = $this->getRedirectAfterLoginProvider(['isAuthenticated']);
        $redirectAfterLoginProvider->expects($this->once())
            ->method('isAuthenticated')
            ->willReturn(true);

        $redirectAfterLoginProvider->onKernelResponse($event);

        $this->assertInstanceOf(RedirectResponse::class, $event->getResponse());

        $this->assertSame(static::REDIRECT_URL_VALID, $event->getResponse()->headers->get('location'));
    }

    /**
     * @param array $methods
     *
     * @return \PHPUnit\Framework\MockObject\MockObject|\Spryker\Zed\Auth\Communication\Plugin\ServiceProvider\RedirectAfterLoginProvider
     */
    protected function getRedirectAfterLoginProvider(array $methods = []): RedirectAfterLoginProvider
    {
        if (!$methods) {
            return new RedirectAfterLoginProvider();
        }

        return $this->getMockBuilder(RedirectAfterLoginProvider::class)
            ->setMethods($methods)
            ->getMock();
    }

    /**
     * @return \Symfony\Component\HttpKernel\HttpKernelInterface|\PHPUnit\Framework\MockObject\MockObject
     */
    protected function getHttpKernel(): HttpKernelInterface
    {
        return $this->getMockBuilder(HttpKernelInterface::class)->getMock();
    }
}
