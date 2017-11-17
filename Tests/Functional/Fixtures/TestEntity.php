<?php
namespace Netlogix\Cqrs\Tests\Functional\Fixtures;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

use Neos\Flow\Annotations as Flow;
use Doctrine\ORM\Mapping as ORM;

/**
 * @Flow\Entity
 */

class TestEntity {

	/**
	 * @var string
	 */
	protected $foo;

	/**
	 * @return string
	 */
	public function getFoo() {
		return $this->foo;
	}

	/**
	 * @param string $foo
	 */
	public function setFoo($foo) {
		$this->foo = $foo;
	}
}