<?php

declare(strict_types=1);

namespace MoonShine\Laravel\Components\Layout;

use Closure;
use MoonShine\Support\Enums\FormMethod;
use MoonShine\UI\Components\ActionButton;
use MoonShine\UI\Components\FormBuilder;
use MoonShine\UI\Components\Layout\Div;
use MoonShine\UI\Components\MoonShineComponent;
use MoonShine\UI\Fields\Text;

/**
 * @method static static make(string $key = 'search', string $action = '', string $placeholder = '', bool $isEnabled = false)
 */
final class Search extends MoonShineComponent
{
    protected string $view = 'moonshine::components.layout.search';

    protected ?Closure $modifyForm = null;

    protected ?Closure $modifyInput = null;

    protected string $form = '';

    public function __construct(
        private readonly string $key = 'search',
        private string $action = '',
        private string $placeholder = '',
        private bool $isEnabled = false,
    ) {
        parent::__construct();

        if ($this->placeholder === '') {
            $this->placeholder = __('moonshine::ui.search') . ' (Ctrl+K)';
        }
    }

    public function enabled(): static
    {
        $this->isEnabled = true;

        return $this;
    }

    protected function isSearchEnabled(): bool
    {
        if ($this->isEnabled) {
            return true;
        }

        $resource = moonshineRequest()->getResource();

        return ! \is_null($resource) && $resource->hasSearch();
    }

    /**
     * @param  Closure(FormBuilder $form, self $ctx): FormBuilder  $callback
     */
    public function modifyForm(Closure $callback): self
    {
        $this->modifyForm = $callback;

        return $this;
    }

    /**
     * @param  Closure(Text $input, self $ctx): Text  $callback
     */
    public function modifyInput(Closure $callback): self
    {
        $this->modifyInput = $callback;

        return $this;
    }

    protected function getInput(): Text
    {
        $input = Text::make($this->placeholder, $this->key)
            ->setAttribute('type', 'search')
            ->xModel('searchValue')
            ->class('search-form-field')
            ->required()
            ->placeholder($this->placeholder)
            ->withoutWrapper()
            ->customAttributes([
                'x-ref' => 'searchInput',
                '@keyup.ctrl.k.window' => '$refs.searchInput.focus()',
                '@keyup.ctrl.period.window' => '$refs.searchInput.focus()',
                '@input.debounce.300ms'=>'$refs.searchInput.value == "" ? $refs.searchForm.submit(): ""'
            ]);

        if (! \is_null($this->modifyInput)) {
            $input = \call_user_func($this->modifyInput, $input, $this);
        }

        return $input;
    }

    protected function getForm(): FormBuilder
    {
        $value = moonshine()->getRequest()->getScalar($this->key, '');

        $form = FormBuilder::make($this->action, FormMethod::GET)
            ->customAttributes([
                'x-ref' => 'searchForm',
            ])
            ->rawMode()
            ->class('search-form')
            ->fields([
                Div::make([
                    $this->getInput(),

                    ActionButton::make('')
                        ->rawMode()
                        ->onClick(fn (): string => 'searchValue = ""; $refs.searchInput.value = ""; $refs.searchForm.submit()')
                        ->class('search-form-clear')
                        ->xShow('searchValue', '!=', '')
                        ->customAttributes([
                            'type' => 'button',
                        ])
                        ->icon('x-mark'),

                    ActionButton::make('')
                        ->rawMode()
                        ->customAttributes([
                            'type' => 'submit',
                        ])
                        ->class('search-form-submit')
                        ->icon('magnifying-glass'),
                ])
                    ->style('display: inline')
                    ->xData(['searchValue' => $value]),
            ])
            ->hideSubmit();

        if (! \is_null($this->modifyForm)) {
            $form = \call_user_func($this->modifyForm, $form, $this);
        }

        return $form;
    }

    protected function prepareBeforeRender(): void
    {
        $url = moonshineRequest()->getResource()?->getUrl();

        if ($url !== null && $this->isSearchEnabled()) {
            $this->action = $url;
        }

        $this->form = (string) $this->getForm();
        $this->isEnabled = $this->isSearchEnabled();
    }

    /**
     * @return array<string, mixed>
     */
    protected function viewData(): array
    {
        return [
            'isEnabled' => $this->isEnabled,
            'form' => $this->form,
        ];
    }
}
