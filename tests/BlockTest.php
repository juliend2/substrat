<?php

use PHPUnit\Framework\TestCase;

use Julien\Substrat\Substrat;

function normalizeHtmlWhitespace(string $html): string {
	// Remove whitespace between tags
	$html = preg_replace('/>\s+</', '><', $html);

	// Remove whitespace right after an opening tag
	$html = preg_replace('/>\s+([^<])/', '>$1', $html);

	// Remove whitespace right before a closing tag
	$html = preg_replace('/([^>])\s+</', '$1<', $html);

	return trim($html);
}


class BlockTest extends TestCase
{
	protected $template;

	function setup():void {
		$this->template = "";
	}

	function testOneBlockReplacement()
	{
		$substrat = new Substrat(
			"
			<div>
				{{ test.test.pass }}
			</div>
			",
			[
				"test" => [
					"test"=>[
						"pass"=>"Pass"
					]
				]
			]
		);

		$this->assertEquals(
			normalizeHtmlWhitespace("
			 <div>
				 Pass
			 </div>"),
			normalizeHtmlWhitespace($substrat->replaceAll())
		);
	}


	function testManyBlockReplacements()
	{
		$substrat = new Substrat(
			"
			<div>
				{{ test.test.one }}
				<br>
				{{ test.test.two }}
			</div>
			",
			[
				"test" => [
					"test"=>[
						"one"=>"One",
						"two"=>"Two",
					]
				]
			]
		);

		$this->assertEquals(
			normalizeHtmlWhitespace("
			 <div>
				 One<br>Two
			 </div>"),
			normalizeHtmlWhitespace($substrat->replaceAll())
		);
	}

	function testThreeBlockReplacements()
	{
		$substrat = new Substrat(
			"
			<div>
				{{ test.test.one }}
				<br>
				{{ test.test.two }}
				<br>
				{{ test.test.three }}
			</div>
			",
			[
				"test" => [
					"test"=>[
						"one"=>"One",
						"two"=>"Two",
						"three"=>"Three",
					]
				]
			]
		);

		$this->assertEquals(
			normalizeHtmlWhitespace("
			 <div>
				 One<br>Two<br>Three
			 </div>"),
			normalizeHtmlWhitespace($substrat->replaceAll())
		);
	}

}
