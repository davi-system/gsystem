<?php
declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (http://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (http://cakefoundation.org)
 * @link          http://cakephp.org CakePHP(tm) Project
 * @since         DebugKit 0.1
 * @license       http://www.opensource.org/licenses/mit-license.php MIT License
 */
namespace DebugKit\Test\TestCase\View\Helper;

use Cake\Error\Debug\TextFormatter;
use Cake\Error\Debugger;
use Cake\Http\ServerRequest as Request;
use Cake\Routing\Router;
use Cake\TestSuite\TestCase;
use Cake\View\View;
use DebugKit\View\Helper\ToolbarHelper;
use SimpleXmlElement;
use stdClass;

/**
 * Class ToolbarHelperTestCase
 */
class ToolbarHelperTest extends TestCase
{
    /**
     * @var View
     */
    protected $View;

    /**
     * @var ToolbarHelper
     */
    protected $Toolbar;

    /**
     * Setup
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        Router::createRouteBuilder('/')->connect('/{controller}/{action}');

        $request = new Request();
        $request = $request->withParam('controller', 'pages')->withParam('action', 'display');

        $this->View = new View($request);
        $this->Toolbar = new ToolbarHelper($this->View);
    }

    /**
     * Tear Down
     *
     * @return void
     */
    public function tearDown(): void
    {
        parent::tearDown();
        unset($this->Toolbar);
    }

    public function testDumpNodesSorted()
    {
        $path = '//*[@class="cake-dbg-array-item"]/*[@class="cake-dbg-string"]';
        $data = ['z' => 1, 'a' => 99, 'm' => 123];
        $nodes = array_map(function ($v) {
            return Debugger::exportVarAsNodes($v);
        }, $data);
        $result = $this->Toolbar->dumpNodes($nodes);
        $xml = new SimpleXmlElement($result);
        $elements = $xml->xpath($path);
        $this->assertSame(["'z'", "'a'", "'m'"], array_map('strval', $elements));

        $this->Toolbar->setSort(true);
        $result = $this->Toolbar->dumpNodes($nodes);
        $xml = new SimpleXmlElement($result);
        $elements = $xml->xpath($path);
        $this->assertSame(["'a'", "'m'", "'z'"], array_map('strval', $elements));
    }

    public function testDumpCoerceHtml()
    {
        $restore = Debugger::configInstance('exportFormatter');
        Debugger::configInstance('exportFormatter', TextFormatter::class);
        $result = $this->Toolbar->dump(false);
        $this->assertMatchesRegularExpression('/<\w/', $result, 'Contains HTML tags.');
        $this->assertSame(
            TextFormatter::class,
            Debugger::configInstance('exportFormatter'),
            'Should restore setting'
        );

        // Restore back to original value.
        Debugger::configInstance('exportFormatter', $restore);
    }

    public function testDumpSorted()
    {
        $path = '//*[@class="cake-dbg-array-item"]/*[@class="cake-dbg-string"]';
        $data = ['z' => 1, 'a' => 99, 'm' => 123];
        $result = $this->Toolbar->dump($data);
        $xml = new SimpleXmlElement($result);
        $elements = $xml->xpath($path);
        $this->assertSame(["'z'", "'a'", "'m'"], array_map('strval', $elements));

        $this->Toolbar->setSort(true);
        $result = $this->Toolbar->dump($data);
        $xml = new SimpleXmlElement($result);
        $elements = $xml->xpath($path);
        $this->assertSame(["'a'", "'m'", "'z'"], array_map('strval', $elements));
    }

    /**
     * Test makeNeatArray with basic types.
     *
     * @return void
     */
    public function testMakeNeatArrayBasic()
    {
        $in = false;
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', '0', '/strong', ' (false)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = null;
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', '0', '/strong', ' (null)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = true;
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', '0', '/strong', ' (true)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = [];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', '0', '/strong', ' (empty)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test that cyclic references can be printed.
     *
     * @return void
     */
    public function testMakeNeatArrayCyclicObjects()
    {
        $a = new stdClass();
        $b = new stdClass();
        $a->child = $b;
        $b->parent = $a;

        $in = ['obj' => $a];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            ['ul' => ['class' => 'neat-array depth-0']],
            '<li', '<strong', 'obj', '/strong', ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-1']],
            '<li', '<strong', 'child', '/strong', ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-2']],
            '<li', '<strong', 'parent', '/strong',
            ' (stdClass) - recursion',
            '/li',
            '/ul',
            '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test that duplicate references can be printed.
     *
     * @return void
     */
    public function testMakeNeatArrayDuplicateObjects()
    {
        $a = new stdClass();
        $b = new stdClass();
        $a->first = $b;
        $a->second = $b;

        $in = ['obj' => $a];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            ['ul' => ['class' => 'neat-array depth-0']],
            '<li', '<strong', 'obj', '/strong', ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-1']],
            '<li', '<strong', 'first', '/strong', ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-2']],
            '/ul',
            '/li',
            '<li', '<strong', 'second', '/strong', ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-2']],
            '/ul',
            '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test Neat Array formatting
     *
     * @return void
     */
    public function testMakeNeatArray()
    {
        $in = ['key' => 'value'];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = ['key' => null];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', 'key', '/strong', ' (null)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = ['key' => 'value', 'foo' => 'bar'];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'foo', '/strong', ' bar', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = [
            'key' => 'value',
            'foo' => [
                'this' => 'deep',
                'another' => 'value',
            ],
        ];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'foo', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1']],
                '<li', '<strong', 'this', '/strong', ' deep', '/li',
                '<li', '<strong', 'another', '/strong', ' value', '/li',
                '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = [
            'key' => 'value',
            'foo' => [
                'this' => 'deep',
                'another' => 'value',
            ],
            'lotr' => [
                'gandalf' => 'wizard',
                'bilbo' => 'hobbit',
            ],
        ];
        $result = $this->Toolbar->makeNeatArray($in, 1);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0 expanded'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'foo', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1']],
                '<li', '<strong', 'this', '/strong', ' deep', '/li',
                '<li', '<strong', 'another', '/strong', ' value', '/li',
                '/ul',
            '/li',
            '<li', '<strong', 'lotr', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1']],
                '<li', '<strong', 'gandalf', '/strong', ' wizard', '/li',
                '<li', '<strong', 'bilbo', '/strong', ' hobbit', '/li',
                '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $result = $this->Toolbar->makeNeatArray($in, 2);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0 expanded'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'foo', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1 expanded']],
                '<li', '<strong', 'this', '/strong', ' deep', '/li',
                '<li', '<strong', 'another', '/strong', ' value', '/li',
                '/ul',
            '/li',
            '<li', '<strong', 'lotr', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1 expanded']],
                '<li', '<strong', 'gandalf', '/strong', ' wizard', '/li',
                '<li', '<strong', 'bilbo', '/strong', ' hobbit', '/li',
                '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);

        $in = ['key' => 'value', 'array' => []];
        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0'],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'array', '/strong', ' (empty)', '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test makeNeatArray with object inputs.
     *
     * @return void
     */
    public function testMakeNeatArrayObjects()
    {
        $in = new stdClass();
        $in->key = 'value';
        $in->nested = new stdClass();
        $in->nested->name = 'mark';

        $result = $this->Toolbar->makeNeatArray($in);
        $expected = [
            ['ul' => ['class' => 'neat-array depth-0']],
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'nested', '/strong',
            ' (stdClass)',
            ['ul' => ['class' => 'neat-array depth-1']],
            '<li', '<strong', 'name', '/strong', ' mark', '/li',
            '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }

    /**
     * Test makeNeatArray sorted
     *
     * @return void
     */
    public function testMakeNeatArraySorted()
    {
        $in = [
            'lotr' => [
                'gandalf' => 'wizard',
                'bilbo' => 'hobbit',
            ],
            'key' => 'value',
            'foo' => [
                'this' => 'deep',
                'another' => 'value',
            ],
        ];
        $this->Toolbar->setSort(true);
        $result = $this->Toolbar->makeNeatArray($in, 1);
        $expected = [
            'ul' => ['class' => 'neat-array depth-0 expanded'],
            '<li', '<strong', 'foo', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1']],
                '<li', '<strong', 'this', '/strong', ' deep', '/li',
                '<li', '<strong', 'another', '/strong', ' value', '/li',
                '/ul',
            '/li',
            '<li', '<strong', 'key', '/strong', ' value', '/li',
            '<li', '<strong', 'lotr', '/strong',
                ' (array)',
                ['ul' => ['class' => 'neat-array depth-1']],
                '<li', '<strong', 'gandalf', '/strong', ' wizard', '/li',
                '<li', '<strong', 'bilbo', '/strong', ' hobbit', '/li',
                '/ul',
            '/li',
            '/ul',
        ];
        $this->assertHtml($expected, $result);
    }
}
