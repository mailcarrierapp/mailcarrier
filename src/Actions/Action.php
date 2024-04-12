<?php

namespace MailCarrier\Actions;

use Illuminate\Support\Facades\App;
use MailCarrier\Concerns\Resolvable;
use Mockery;
use Mockery\Expectation;
use Mockery\ExpectationInterface;
use Mockery\HigherOrderMessage;
use Mockery\LegacyMockInterface;
use Mockery\MockInterface;

/**
 * Original code taken and edited from `AsFake` trait
 * from the library "Laravel Actions" by @lorisleiva.
 *
 * @see https://github.com/lorisleiva/laravel-actions/blob/4e60c9fbdfcea7d9977d882f3104c8996b43c169/src/Concerns/AsFake.php
 */
abstract class Action
{
    use Resolvable;

    public static function mock(): MockInterface|LegacyMockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        /** @var \Mockery\MockInterface $mock */
        $mock = Mockery::mock(static::class);
        $mock->shouldAllowMockingProtectedMethods();

        return static::setFakeResolvedInstance($mock);
    }

    public static function spy(): MockInterface
    {
        if (static::isFake()) {
            return static::getFakeResolvedInstance();
        }

        /** @var \Mockery\MockInterface $spy */
        $spy = Mockery::spy(static::class);

        return static::setFakeResolvedInstance($spy);
    }

    public static function partialMock(): MockInterface|LegacyMockInterface
    {
        return static::mock()->makePartial();
    }

    /**
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public static function shouldRun()
    {
        return static::mock()->shouldReceive('run');
    }

    /**
     * @return Expectation|ExpectationInterface|HigherOrderMessage
     */
    public static function shouldNotRun()
    {
        return static::mock()->shouldNotReceive('run');
    }

    /**
     * @return Expectation|ExpectationInterface|HigherOrderMessage|MockInterface
     */
    public static function allowToRun()
    {
        return static::spy()->allows('run');
    }

    public static function isFake(): bool
    {
        return App::make(static::class) instanceof MockInterface; // @phpstan-ignore-line
    }

    protected static function setFakeResolvedInstance(MockInterface $fake): MockInterface
    {
        return App::getFacadeApplication()->instance(static::class, $fake);
    }

    protected static function getFakeResolvedInstance(): ?MockInterface
    {
        return App::make(static::class);
    }
}
