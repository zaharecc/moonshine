<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use MoonShine\Support\DTOs\Select\Option;
use MoonShine\Support\DTOs\Select\OptionImage;
use MoonShine\Support\DTOs\Select\OptionProperty;
use MoonShine\Support\DTOs\Select\Options;
use MoonShine\Support\Enums\ObjectFit;
use MoonShine\Tests\Fixtures\Resources\TestResourceBuilder;
use MoonShine\UI\Fields\Select;

uses()->group('fields');

beforeEach(function (): void {
    $this->selectOptions = [
        0 => 1,
        1 => 2,
        2 => 3,
    ];

    $this->field = Select::make('Select')->options($this->selectOptions);

    $this->fieldMultiple = Select::make('Select multiple')
        ->options($this->selectOptions)
        ->multiple();

    $this->item = new class () extends Model {
        public int $select = 1;
        public array $select_multiple = [1];

        protected $casts = [
            'select_multiple' => 'json',
        ];
    };

    fillFromModel($this->field, $this->item);
    fillFromModel($this->fieldMultiple, $this->item);
});

describe('basic methods', function () {
    it('type', function (): void {
        expect($this->field->getAttributes()->get('type'))
            ->toBeEmpty();
    });

    it('view', function (): void {
        expect($this->field->getView())
            ->toBe('moonshine::fields.select');
    });

    it('preview', function (): void {
        expect((string) $this->field->previewMode())
            ->toBe('2')
            ->and((string) $this->fieldMultiple->withoutWrapper())
            ->toBe(
                view('moonshine::fields.select', $this->fieldMultiple->toArray())->render()
            );
    });

    it('change preview', function () {
        expect($this->field->changePreview(static fn () => 'changed'))
            ->preview()
            ->toBe('changed');
    });

    it('default value', function () {
        $field = Select::make('Select')->options([
            1 => 1,
            2 => 2,
        ])->default(2);

        expect($field->toValue())
            ->toBe(2);
    });

    it('applies', function () {
        expect()
            ->applies($this->field);
    });

    it('multiple', function (): void {
        expect($this->field->isMultiple())
            ->toBeFalse()
            ->and($this->fieldMultiple->isMultiple())
            ->toBeTrue();
    });

    it('searchable', function (): void {
        expect($this->fieldMultiple)
            ->isSearchable()
            ->toBeFalse()
            ->and($this->fieldMultiple->searchable())
            ->isSearchable()
            ->toBeTrue();
    });

    it('options', function (): void {
        expect($this->fieldMultiple)
            ->getValues()->toArray()
            ->toBe((new Options(
                $this->selectOptions,
                [1]
            ))->toArray());
    });

    it('is selected correctly', function (): void {
        expect($this->fieldMultiple)
            ->getValues()
            ->isSelected('1')
            ->toBeTrue();
    });

    it('is selected invalid', function (): void {
        expect($this->fieldMultiple)
            ->getValues()
            ->isSelected('2')
            ->toBeFalse();
    });

    it('is selected grouped correctly', function (): void {
        expect(Select::make('Select')->options(
            ['Group 1' => [1 => 1], 'Group 2' => [2 => 2]]
        )->default(2)->toValue())
            ->toBe(2);
    });

    it('names single', function (): void {
        expect($this->field)
            ->getNameAttribute()
            ->toBe('select')
            ->getNameAttribute('1')
            ->toBe('select');
    });

    it('names multiple', function (): void {
        expect($this->fieldMultiple)
            ->getNameAttribute()
            ->toBe('select_multiple[]')
            ->getNameAttribute('1')
            ->toBe('select_multiple[1]');
    });

    it('apply', function (): void {
        $data = ['select' => 1];

        fakeRequest(method: 'post', parameters: $data);

        expect(
            $this->field->apply(
                TestResourceBuilder::new()->fieldApply($this->field),
                new class () extends Model {
                    protected $fillable = [
                        'select',
                    ];
                }
            )
        )
            ->toBeInstanceOf(Model::class)
            ->select
            ->toBe($data['select'])
        ;
    });

    it('apply multiple', function (): void {
        $data = ['select' => [1,2]];

        fakeRequest(method: 'post', parameters: $data);

        expect(
            $this->field->apply(
                TestResourceBuilder::new()->fieldApply($this->field),
                new class () extends Model {
                    protected $fillable = [
                        'select',
                    ];
                }
            )
        )
            ->toBeInstanceOf(Model::class)
            ->select
            ->toBe($data['select'])
        ;
    });
});

describe('select field with images', function () {
    beforeEach(function (): void {
        $optionsWithImages = new Options([
            new Option(
                'Image 1',
                '1',
                properties: new OptionProperty(
                    new OptionImage(
                        src: 'image1.jpg',
                        width: 5,
                        height: 5,
                        objectFit: ObjectFit::COVER
                    )
                )
            ),
            new Option(
                'Image 2',
                '2',
                properties: new OptionProperty(
                    new OptionImage(
                        src: 'image2.png',
                        width: 8,
                        objectFit: ObjectFit::CONTAIN
                    )
                )
            )
        ]);

        $this->fieldWithImages = Select::make('Select field with images')
            ->options($optionsWithImages)
            ->default('2')
            ->multiple();
    });

    it('renders images in options', function () {
        $result = $this->fieldWithImages->toArray();

        expect($result['values'][1]['properties']['image'])->toBe([
            'src' => 'image1.jpg',
            'width' => 5,
            'height' => 5,
            'objectFit' => 'cover'
        ])->and($result['values'][2]['properties']['image'])->toBe([
            'src' => 'image2.png',
            'width' => 8,
            'height' => 10,
            'objectFit' => 'contain'
        ]);
    });

    it('handles invalid image types', function () {
        $field = Select::make('Invalid images')
            ->options([
                1 => 'invalid_string_image',
                new \stdClass()
            ]);

        expect(fn() => $field->previewMode())
            ->not()->toThrow(Exception::class);
    });
});
