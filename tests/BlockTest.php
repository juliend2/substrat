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
  protected $content;
	function setup():void {
		$this->template = "
			<div>
			  {{ test.test.pass }}
			</div>
";
		$this->content = [
			"test" => [
				"test"=>[
					"pass"=>"Pass"
				]
			]
		];
	}

	function testBlockReplacement()
	{
		$substrat = new Substrat(
			$this->template,
			$this->content
		);

		$this->assertEquals(
			normalizeHtmlWhitespace("<div>
			   Pass
			 </div>"),
			normalizeHtmlWhitespace($substrat->replaceAll())
		);
	}

}
