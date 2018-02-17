<?php

namespace Maatwebsite\Excel\Tests\Drivers\PhpSpreadsheet;

use Countable;
use IteratorAggregate;
use PHPUnit\Framework\TestCase;
use Maatwebsite\Excel\Configuration;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Row;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Sheet;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Column;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Reader;
use Maatwebsite\Excel\Tests\Drivers\IterableTestCase;
use Maatwebsite\Excel\Tests\Drivers\CountableTestCase;
use Maatwebsite\Excel\Drivers\PhpSpreadsheet\Loaders\DefaultLoader;

class SheetTest extends TestCase
{
    use IterableTestCase, CountableTestCase;

    /**
     * @var string
     */
    protected static $simpleXlsx = __DIR__ . '/../../_data/simple_xlsx.xlsx';

    /**
     * @var Row
     */
    protected static $cachedSheet;

    /**
     * @var Configuration
     */
    protected static $defaultConfig;

    /**
     * @var Sheet
     */
    protected $sheet;

    public static function setUpBeforeClass()
    {
        static::$cachedSheet = (new Reader(new Configuration(), new DefaultLoader()))
            ->load(static::$simpleXlsx)
            ->sheetByIndex(0);
    }

    public function setUp()
    {
        parent::setUp();

        $this->sheet = clone static::$cachedSheet;

        // Reset settings
        $this->sheet->useRowAsHeading(false);
    }

    /**
     * @test
     */
    public function sheet_can_get_sheet_title()
    {
        $this->assertEquals('Simple', $this->sheet->getTitle());
    }

    /**
     * @test
     */
    public function sheet_can_get_sheet_index()
    {
        $this->assertEquals(0, $this->sheet->getSheetIndex());
    }

    /**
     * @test
     */
    public function sheet_can_iterate_over_rows()
    {
        // Traversable
        foreach ($this->sheet as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }

        // Method
        foreach ($this->sheet->rows() as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }

        // Iterator
        foreach ($this->sheet->getIterator() as $row) {
            $this->assertInstanceOf(Row::class, $row);
        }
    }

    /**
     * @test
     */
    public function sheet_can_iterate_over_columns()
    {
        // Method
        foreach ($this->sheet->columns() as $column) {
            $this->assertInstanceOf(Column::class, $column);
        }

        // Iterator
        foreach ($this->sheet->getColumnIterator() as $column) {
            $this->assertInstanceOf(Column::class, $column);
        }
    }

    /**
     * @test
     */
    public function sheet_can_set_start_and_end_row()
    {
        $this->sheet->setStartRow(2);
        $this->sheet->setEndRow(5);

        $count = 0;
        foreach ($this->sheet as $row) {
            $count++;
            $this->assertInstanceOf(Row::class, $row);
        }

        $this->assertEquals(4, $count);
    }

    /**
     * @test
     */
    public function sheet_can_iterate_rows_starting_on_a_certain_row()
    {
        $count = 0;
        $start = 2;
        foreach ($this->sheet->rows(2, 5) as $row) {
            $this->assertInstanceOf(Row::class, $row);
            $this->assertEquals($start, $row->getRowNumber());
            $count++;
            $start++;
        }

        $this->assertEquals(4, $count);
    }

    /**
     * @test
     */
    public function sheet_can_iterate_columns_starting_on_a_certain_column()
    {
        $count = 0;
        $start = 'B';
        foreach ($this->sheet->columns('B', 'D') as $column) {
            $this->assertInstanceOf(Column::class, $column);
            $this->assertEquals($start, $column->getColumnIndex());
            $count++;
            $start++;
        }

        $this->assertEquals(3, $count);
    }

    /**
     * @test
     */
    public function sheet_can_get_column_by_index()
    {
        $column = $this->sheet->column('D');

        $this->assertInstanceOf(Column::class, $column);
        $this->assertEquals('D', $column->getColumnIndex());
    }

    /**
     * @test
     */
    public function sheet_can_count_rows()
    {
        $this->assertCount(11, $this->sheet);
        $this->assertSame(11, count($this->sheet));
        $this->assertSame(11, $this->sheet->count());
    }

    /**
     * @test
     * @incomplete
     */
    public function sheet_can_count_columns()
    {
        $this->assertSame(4, $this->sheet->columnCount());
    }

    /**
     * @test
     */
    public function can_get_highest_column()
    {
        $this->assertEquals('D', $this->sheet->getHighestColumn());
    }

    /**
     * @test
     */
    public function sheet_can_get_row_by_row_number()
    {
        $row = $this->sheet->row(10);

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals(10, $row->getRowNumber());
    }

    /**
     * @test
     */
    public function sheet_can_get_first_row()
    {
        $row = $this->sheet->first();

        $this->assertInstanceOf(Row::class, $row);
        $this->assertEquals(1, $row->getRowNumber());
    }

    /**
     * @test
     */
    public function sheet_can_convert_itself_to_array()
    {
        $this->assertEquals(
            [
                ['A1', 'B1', 'C1', 'D1'],
                ['A2', 'B2', 'C2', 'D2'],
                ['A3', 'B3', 'C3', 'D3'],
                ['A4', 'B4', 'C4', 'D4'],
                ['A5', 'B5', 'C5', 'D5'],
                ['A6', 'B6', 'C6', 'D6'],
                ['A7', 'B7', 'C7', 'D7'],
                ['A8', 'B8', 'C8', 'D8'],
                ['A9', 'B9', 'C9', 'D9'],
                ['A10', 'B10', 'C10', 'D10'],
                ['A11', 'B11', 'C11', 'D11'],
            ],
            $this->sheet->toArray()
        );
    }

    /**
     * @test
     */
    public function sheet_covert_itself_with_headings_to_array()
    {
        $this->sheet->useFirstRowAsHeading();

        $this->assertEquals(
            [
                ['A1' => 'A2', 'B1' => 'B2', 'C1' => 'C2', 'D1' => 'D2'],
                ['A1' => 'A3', 'B1' => 'B3', 'C1' => 'C3', 'D1' => 'D3'],
                ['A1' => 'A4', 'B1' => 'B4', 'C1' => 'C4', 'D1' => 'D4'],
                ['A1' => 'A5', 'B1' => 'B5', 'C1' => 'C5', 'D1' => 'D5'],
                ['A1' => 'A6', 'B1' => 'B6', 'C1' => 'C6', 'D1' => 'D6'],
                ['A1' => 'A7', 'B1' => 'B7', 'C1' => 'C7', 'D1' => 'D7'],
                ['A1' => 'A8', 'B1' => 'B8', 'C1' => 'C8', 'D1' => 'D8'],
                ['A1' => 'A9', 'B1' => 'B9', 'C1' => 'C9', 'D1' => 'D9'],
                ['A1' => 'A10', 'B1' => 'B10', 'C1' => 'C10', 'D1' => 'D10'],
                ['A1' => 'A11', 'B1' => 'B11', 'C1' => 'C11', 'D1' => 'D11'],
            ],
            $this->sheet->toArray()
        );
    }

    /**
     * @test
     */
    public function sheet_covert_itself_with_headings_to_array_with_custom_start_end_row()
    {
        $this->sheet->useFirstRowAsHeading();
        $this->sheet->setStartRow(1); // because of the headings as first row, it will skip that one
        $this->sheet->setEndRow(3);

        $this->assertEquals(
            [
                ['A1' => 'A2', 'B1' => 'B2', 'C1' => 'C2', 'D1' => 'D2'],
                ['A1' => 'A3', 'B1' => 'B3', 'C1' => 'C3', 'D1' => 'D3'],
            ],
            $this->sheet->toArray()
        );
    }

    /**
     * @test
     */
    public function sheet_can_have_a_different_row_as_heading_row()
    {
        $this->sheet->useRowAsHeading(3);

        $this->assertEquals(['A' => 'A3', 'B' => 'B3', 'C' => 'C3', 'D' => 'D3'], $this->sheet->getHeadings());

        $this->assertEquals(
            [
                ['A3' => 'A4', 'B3' => 'B4', 'C3' => 'C4', 'D3' => 'D4'],
                ['A3' => 'A5', 'B3' => 'B5', 'C3' => 'C5', 'D3' => 'D5'],
                ['A3' => 'A6', 'B3' => 'B6', 'C3' => 'C6', 'D3' => 'D6'],
                ['A3' => 'A7', 'B3' => 'B7', 'C3' => 'C7', 'D3' => 'D7'],
                ['A3' => 'A8', 'B3' => 'B8', 'C3' => 'C8', 'D3' => 'D8'],
                ['A3' => 'A9', 'B3' => 'B9', 'C3' => 'C9', 'D3' => 'D9'],
                ['A3' => 'A10', 'B3' => 'B10', 'C3' => 'C10', 'D3' => 'D10'],
                ['A3' => 'A11', 'B3' => 'B11', 'C3' => 'C11', 'D3' => 'D11'],
            ],
            $this->sheet->toArray()
        );
    }

    /**
     * @test
     */
    public function sheet_skips_first_row_if_its_the_heading_row()
    {
        $this->sheet->useFirstRowAsHeading();

        $expectedRowIndex = 2;
        foreach ($this->sheet->rows() as $row) {
            $this->assertEquals($expectedRowIndex, $row->getRowNumber());
            $expectedRowIndex++;
        }
    }

    /**
     * @test
     */
    public function can_check_if_cell_exists_on_sheet()
    {
        $this->assertTrue($this->sheet->hasCell('B10'));
        $this->assertFalse($this->sheet->hasCell('ZZZ1000'));
    }

    /**
     * @test
     */
    public function can_find_cell_by_coordinate()
    {
        $cell = $this->sheet->cell('B10');
        $this->assertEquals('B10', $cell->getCoordinate());
    }

    /**
     * @test
     */
    public function sheet_headings_will_be_empty_when_heading_row_disabled()
    {
        $headings = $this->sheet->getHeadings();

        $this->assertEquals([], $headings);
    }

    /**
     * @test
     */
    public function can_get_headings_of_sheet()
    {
        $this->sheet->useFirstRowAsHeading();
        $headings = $this->sheet->getHeadings();

        $this->assertEquals(['A' => 'A1', 'B' => 'B1', 'C' => 'C1', 'D' => 'D1'], $headings);
    }

    /**
     * @test
     */
    public function sheet_caches_heading_row()
    {
        $this->sheet->useFirstRowAsHeading();

        $this->assertEquals($this->sheet->getHeadings(), $this->sheet->getHeadings());
    }

    /**
     * @return IteratorAggregate
     */
    public function getIterable()
    {
        return $this->sheet;
    }

    /**
     * @return Countable
     */
    public function getCountable()
    {
        return $this->sheet;
    }
}