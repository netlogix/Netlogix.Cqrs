<?php
namespace Netlogix\Cqrs\Log;

/*
 * This file is part of the Netlogix.Cqrs package.
 */

class ExceptionData
{

	/**
	 * @var string
	 */
	protected $message;

	/**
	 * @var int
	 */
	protected $code;

	/**
	 * @var string
	 */
	protected $file;

	/**
	 * @var int
	 */
	protected $line;

	/**
	 * @var string
	 */
	protected $trace;

	public function __construct(\Exception $exception)
	{
		$this->message = $exception->getMessage();
		$this->code = $exception->getCode();
		$this->file = $exception->getFile();
		$this->line = $exception->getLine();
		$this->trace = $exception->getTraceAsString();
	}

	/**
	 * @return string
	 */
	public function getMessage()
	{
		return $this->message;
	}

	/**
	 * @return int
	 */
	public function getCode()
	{
		return $this->code;
	}

	/**
	 * @return string
	 */
	public function getFile()
	{
		return $this->file;
	}

	/**
	 * @return int
	 */
	public function getLine()
	{
		return $this->line;
	}

	/**
	 * @return string
	 */
	public function getTrace()
	{
		return $this->trace;
	}

}