<?php namespace GeneaLabs\LaravelCasts;

use Collective\Html\FormBuilder as Form;
use GeneaLabs\LaravelCasts\Traits\CurrentFormBuilderMethods;
use GeneaLabs\LaravelCasts\Traits\CurrentOrLtsLaravelVersion;
use GeneaLabs\LaravelCasts\Traits\LtsFormBuilderMethods;
use Illuminate\Support\HtmlString;
use Illuminate\Support\MessageBag;
use Illuminate\Support\Collection;
use Illuminate\Routing\UrlGenerator;
use Illuminate\View\Factory;

class FormBuilder extends Form
{
    use CurrentFormBuilderMethods;
    use CurrentOrLtsLaravelVersion;
    use LtsFormBuilderMethods;

    protected $errors;
    protected $offset = 0;
    protected $labelWidth = 3;
    protected $fieldWidth = 9;
    protected $isHorizontalForm = false;
    protected $framework; // = 'bootstrap3';

    public function __construct(HtmlBuilder $html, UrlGenerator $url, Factory $view, $csrfToken)
    {
        parent::__construct($html, $url, $view, $csrfToken);

        $this->errors = app('session')->get('errors', new MessageBag());
    }

    private function renderControlForLaravelCurrent(string $type, string $controlHtml, string $name, $value = '', array $options) : string
    {
        return call_user_func_array(
            [$this, "{$this->framework}Control"],
            [$type, $controlHtml, $name, $value, $options, $this->fieldWidth, $this->labelWidth, $this->errors]
        );
    }

    public function token()
    {
        return $this->hidden('_token', csrf_token());
    }

    public function selectRangeWithInterval(string $name, int $start, int $end, int $interval, int $value = null, array $options = []) : string
    {
        if ($interval == 0) {
            return parent::selectRange($name, $start, $end, $value, $options);
        }

        $items = [];
        $items[$value] = $value;
        $startValue = $start;
        $endValue = $end;
        $interval *= ($interval < 0) ? -1 : 1;

        if ($start > $end) {
            $interval *= ($interval > 0) ? -1 : 1;
            $startValue = $end;
            $endValue = $start;
        }

        for ($i=$startValue; $i<$endValue; $i+=$interval) {
            $items[$i . ""] = $i;
        }

        $items[$endValue] = $endValue;

        return $this->select($name, $items, $value, $options);
    }

    public function select($name, $list = [], $selected = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $labelHtml = $this->label($name, array_pull($options, 'label'));
        $controlHtml = parent::select($name, $list, $selected, $options);

        return $this->renderControl('select', $controlHtml, $name, '', $options);
    }

    public function open(array $options = [])
    {
        if (array_key_exists('class', $options) && (strpos($options['class'], 'form-horizontal') !== false)) {
            $this->isHorizontalForm = true;
        }

        if (array_key_exists('offset', $options)) {
            $this->offset = $options['offset'];
        }

        if (array_key_exists('framework', $options)) {
            $this->framework = $options['framework'];
        }

        if ($this->usesBootstrap4()) {
            $this->isHorizontalForm = true;
        }

        if (array_key_exists('labelWidth', $options)) {
            $this->labelWidth = $options['labelWidth'];
        }

        if (array_key_exists('fieldWidth', $options)) {
            $this->fieldWidth = $options['fieldWidth'];
        }

        return parent::open($options);
    }

    public function model($model, array $options = [])
    {
        $this->errors = app('session')->get('errors', new MessageBag());

        if (! $this->errors) {
            $this->errors = new Collection();
        }

        if (array_key_exists('class', $options) && (strpos($options['class'], 'form-horizontal') !== false)) {
            $this->isHorizontalForm = true;
        }

        if (array_key_exists('offset', $options)) {
            $this->offset = $options['offset'];
        }

        if (array_key_exists('labelWidth', $options)) {
            $this->labelWidth = $options['labelWidth'];
        }

        if (array_key_exists('fieldWidth', $options)) {
            $this->fieldWidth = $options['fieldWidth'];
        }

        if (array_key_exists('framework', $options)) {
            $this->framework = $options['framework'];
        }

        return parent::model($model, $options);
    }


    public function label($name, $label = null, $options = [], $escapeHtml = true)
    {
        $label = $label ?? array_pull($options, 'label') ?? '';
        $options = $this->setLabelOptionClasses($options);

        return parent::label($name, $label, $options, $escapeHtml);
    }

    public function text($name, $value = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $controlHtml = parent::text($name, $value, $options);

        return $this->renderControl('text', $controlHtml, $name, $value ?: old($name), $options);
    }

    public function email($name, $value = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $controlHtml = parent::email($name, $value, $options);

        return $this->renderControl('email', $controlHtml, $name, $value ?: old($name), $options);
    }

    public function combobox($name, $list = [], $selected = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $options['multiple'] = '';

        return $this->select($name, $list, $selected ?? old($selected), $options);
    }

    public function password($name, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $labelHtml = $this->label($name, null, $options);
        $controlHtml = parent::password($name, $options);

        return $this->renderControl('password', $controlHtml, $name, '', $options);
    }

    public function url($name, $value = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $controlHtml = parent::url($name, $value, $options);

        return $this->renderControl('url', $controlHtml, $name, $value ?: old($name), $options);
    }

    public function file($name, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control form-control-file']);
        $controlHtml = parent::file($name, $options);

        return $this->renderControl('file', $controlHtml, $name, '', $options);
    }

    public function textarea($name, $value = null, $options = [])
    {
        $options = $this->setOptionClasses($name, $options, ['form-control']);
        $controlHtml = parent::textarea($name, $value, $options);

        return $this->renderControl('textarea', $controlHtml, $name, $value ?: old($name), $options);
    }

    public function checkbox($name, $value = 1, $checked = null, $options = [])
    {
        $additionalClasses = $this->usesBootstrap4() ? 'form-check-input' : '';
        $options = $this->setOptionClasses($name, $options, [$additionalClasses]);
        $label = $options['label'];
        unset($options['label']);
        $controlHtml = parent::checkbox($name, $value, $checked, $options) . " {$label}";

        return $this->renderControl('checkbox', $controlHtml, $name, $value ?: old($name), $options);
    }

    public function submit($value = null, $options = [])
    {
        $cancelUrl = array_key_exists('cancelUrl', $options) ? $options['cancelUrl'] : null;
        $cancelHtml = '';
        $options = $this->setOptionClasses('', $options, ['btn', 'btn-primary']);
        $controlHtml = parent::submit($value, $options);
        unset($options['label']);

        if (! is_null($cancelUrl)) {
            $cancelHtml = link_to($cancelUrl, 'Cancel', ['class' => 'btn btn-cancel pull-right']);
        }

        // TODO: render cancel and reset buttons.
        return $this->renderControl('submit', $controlHtml, '', '', $options);
    }

    public function cancelButton($returnUrl = '')
    {
        return '<a href="' .
                $this->url->previous() . '">' .
                $this->button('Cancel', ['class' => 'btn btn-cancel   pull-right']) .
                '</a>';
    }

    private function setOptionClasses(string $name, array $options, array $addClasses = []) : array
    {
        $classes = [];

        if (array_key_exists('class', $options)) {
            $classes = explode(' ', $options['class']);
        }

        if (count($this->errors)) {
            if ($this->framework === 'bootstrap-4') {
                $classes[] = $this->errors->has($name) ? 'form-control-error' : 'form-control-success';
            }
        }

        foreach ($addClasses as $key => $class) {
            if (! in_array($class, $classes)) {
                $classes[] = $class;
            }
        }

        if (array_key_exists('labelWidth', $options)) {
            $this->labelWidth = $options['labelWidth'];
        }

        if (array_key_exists('fieldWidth', $options)) {
            $this->fieldWidth = $options['fieldWidth'];
        }

        $classes = array_filter($classes);
        $options['class'] = implode(' ', $classes);

        return $options;
    }

    private function usesBootstrap3()
    {
        return ($this->framework === 'bootstrap3');
    }

    private function usesBootstrap4()
    {
        return ($this->framework === 'bootstrap4');
    }

    private function setLabelOptionClasses(array $options)
    {
        $classes = explode(' ', array_get($options, 'class'));

        if ($this->isHorizontalForm) {
            $classes[] = 'col-sm-' . $this->labelWidth;
        }

        if ($this->usesBootstrap3()) {
            $classes[] = 'control-label';
        }

        if ($this->usesBootstrap4() && $this->isHorizontalForm) {
            $classes[] = 'col-form-label';
        }

        $classes = collect($classes)->filter(function ($value, $key = null) {
            $rejects = ['label', 'form-control', 'form-control-error', 'form-control-success', 'form-control-feedback'];

            return (! in_array($value, $rejects));
        });

        $classes = array_filter($classes->toArray());
        $options['class'] = implode(' ', $classes);

        return $options;
    }
}
