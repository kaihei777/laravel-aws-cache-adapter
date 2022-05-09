<?php

namespace LukeWaite\LaravelAwsCacheAdapter\Tests;

use LukeWaite\LaravelAwsCacheAdapter\LaravelCacheAdapter;
use Mockery as m;
use PHPUnit\Framework\TestCase;

class LaravelCacheAdapterTest extends TestCase
{
    /** @var  \Mockery\MockInterface */
    protected $manager;
    protected $repository;

    public function tearDown() :void
    {
        m::close();
    }

    public function setUp() :void
    {
        $this->manager = m::mock('Illuminate\Cache\CacheManager');
        $this->manager->shouldReceive('store')
            ->with('file')
            ->once()
            ->andReturn($this->repository = m::mock('StdClass'));
    }

    public function testGetWithPrefix()
    {
        $this->repository->shouldReceive('get')->with('aws_credentials_testkey')->once()->andReturn('testValue');

        $adapter = new LaravelCacheAdapter($this->manager, 'file', 'test');
        $this->assertEquals('testValue', $adapter->get('key'));
    }

    /**
     * testRemoveWithoutPrefix
     *
     * @doesNotPerformAssertions
     * @return void
     */
    public function testRemoveWithoutPrefix()
    {
        $this->repository->shouldReceive('forget')->with('aws_credentials_key_to_remove')->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->remove('key_to_remove');
    }

    /**
     * testSetLessThan60SecondsRoundsUp
     *
     * @doesNotPerformAssertions
     * @return void
     */
    public function testSetLessThan60SecondsRoundsUp()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 1)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 59);
    }

    /**
     * testSetGreaterThan60SecondsRoundsDown
     *
     * @doesNotPerformAssertions
     * @return void
     */
    public function testSetGreaterThan60SecondsRoundsDown()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 1)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 61);
    }

    /**
     * testSetGreaterThan120SecondsRoundsDown
     *
     * @doesNotPerformAssertions
     * @return void
     */
    public function testSetGreaterThan120SecondsRoundsDown()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 2)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 121);
    }

    /**
     * testSet0Retains0
     *
     * @doesNotPerformAssertions
     * @return void
     */
    public function testSet0Retains0()
    {
        $this->repository->shouldReceive('put')->with('aws_credentials_key', 'value', 0)->once();

        $adapter = new LaravelCacheAdapter($this->manager, 'file', '');
        $adapter->set('key', 'value', 0);
    }
}
