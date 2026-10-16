<?php
/**
 * Teamleader plugin for Craft CMS
 *
 * @link      https://craft-pulse.com
 * @copyright Copyright (c) 2025 CraftPulse
 */

namespace craftpulse\teamleader\fields\formie;

use Craft;
use craft\base\ElementInterface;

use verbb\formie\base\Field;
use verbb\formie\helpers\SchemaHelper;
use verbb\formie\helpers\ValidationMessagesHelper;
use verbb\formie\models\HtmlTag;
use verbb\formie\models\SlotTag;
use verbb\formie\positions\Hidden as HiddenPosition;
use verbb\formie\theme\context\RenderContext;

/**
 * Class ClientType
 *
 * A custom Formie field that allows users to select between Company (B2B) or Client (B2C)
 * request types. This field controls backend logic in the Teamleader Focus integration
 * to determine whether a company should be created alongside the contact.
 *
 * @author CraftPulse
 * @since  5.2.0
 */
class ClientType extends Field
{
    // Const Properties
    // =========================================================================

    public const TYPE_COMPANY = 'company';
    public const TYPE_CONTACT = 'contact';

    // Public Properties
    // =========================================================================

    /**
     * @var array The field options (managed internally, not user-configurable).
     */
    public array $options = [];

    /**
     * @var bool This field is always required.
     */
    public bool $required = true;

    /**
     * @var mixed The default selected option (company or contact).
     */
    public mixed $defaultValue = self::TYPE_COMPANY;

    /**
     * @var string The layout direction for radio buttons (vertical or horizontal).
     */
    public string $layout = 'horizontal';

    /**
     * @var string The label for the Company option.
     */
    public string $companyLabel = 'Company';

    /**
     * @var string The label for the Client option.
     */
    public string $contactLabel = 'Client';

    // Static Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public static function displayName(): string
    {
        return Craft::t('formie', 'Client Type');
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public static function getSvgIconPath(): string
    {
        return 'teamleader-focus/integrations/formie/_formfields/icon-mask.svg';
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public static function phpType(): string
    {
        return 'string';
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getIsRequired(): bool
    {
        return true;
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getFieldTypeDefaults(): array
    {
        return [
            'required' => true,
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getFieldOptions(): array
    {
        return [
            [
                'label' => Craft::t('formie', $this->companyLabel),
                'value' => self::TYPE_COMPANY,
                'isDefault' => $this->defaultValue === self::TYPE_COMPANY,
            ],
            [
                'label' => Craft::t('formie', $this->contactLabel),
                'value' => self::TYPE_CONTACT,
                'isDefault' => $this->defaultValue === self::TYPE_CONTACT,
            ],
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getInputHtml(mixed $value, ?ElementInterface $element = null): string
    {
        return Craft::$app->getView()->renderTemplate('teamleader-focus/integrations/formie/_formfields/client-type/input', [
            'name' => $this->handle,
            'value' => $value,
            'field' => $this,
            'options' => $this->getFieldOptions(),
        ]);
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getPreviewInputHtml(): string
    {
        return Craft::$app->getView()->renderTemplate('teamleader-focus/integrations/formie/_formfields/client-type/preview', [
            'field' => $this,
        ]);
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getFrontEndJsModules(): ?array
    {
        return [
            'src' => Craft::$app->getAssetManager()->getPublishedUrl('@verbb/formie/web/assets/frontend/dist/', true, 'js/fields/checkbox-radio.js'),
            'module' => 'FormieCheckboxRadio',
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function getFormBuilderSettings(): array
    {
        $settings = parent::getFormBuilderSettings();
        $settings['options'] = $this->getFieldOptions();

        // This field cannot be optional
        $settings['required'] = true;

        return $settings;
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineGeneralSchema(): array
    {
        return [
            SchemaHelper::labelField(),
            SchemaHelper::selectField([
                'label' => Craft::t('formie', 'Default Value'),
                'help' => Craft::t('formie', 'The default selected option when the form loads.'),
                'name' => 'defaultValue',
                'options' => [
                    ['label' => Craft::t('formie', 'Company (B2B)'), 'value' => self::TYPE_COMPANY],
                    ['label' => Craft::t('formie', 'Client (B2C)'), 'value' => self::TYPE_CONTACT],
                ],
            ]),
            SchemaHelper::textField([
                'label' => Craft::t('formie', 'Company Label'),
                'help' => Craft::t('formie', 'The label displayed for the Company option.'),
                'name' => 'companyLabel',
            ]),
            SchemaHelper::textField([
                'label' => Craft::t('formie', 'Client Label'),
                'help' => Craft::t('formie', 'The label displayed for the Client option.'),
                'name' => 'contactLabel',
            ]),
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineAppearanceSchema(): array
    {
        return [
            SchemaHelper::visibility(),
            SchemaHelper::selectField([
                'label' => Craft::t('formie', 'Layout'),
                'help' => Craft::t('formie', 'Select which direction to show the options.'),
                'name' => 'layout',
                'options' => [
                    ['label' => Craft::t('formie', 'Vertical'), 'value' => 'vertical'],
                    ['label' => Craft::t('formie', 'Horizontal'), 'value' => 'horizontal'],
                ],
            ]),
            SchemaHelper::labelPosition($this),
            SchemaHelper::instructions(),
            SchemaHelper::instructionsPosition($this),
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineSettingsSchema(): array
    {
        return [
            SchemaHelper::textField([
                'label' => Craft::t('formie', 'Error Message'),
                'help' => Craft::t('formie', 'When validating the form, show this message if an error occurs. Leave empty to retain the default message.'),
                'name' => 'errorMessage',
            ]),
            SchemaHelper::prePopulate(),
            SchemaHelper::includeInEmailField(),
            SchemaHelper::emailNotificationValue([
                'options' => [
                    ['label' => Craft::t('formie', 'Label'), 'value' => 'label'],
                    ['label' => Craft::t('formie', 'Value'), 'value' => 'value'],
                ],
            ]),
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineAdvancedSchema(): array
    {
        return [
            SchemaHelper::handleField(),
            SchemaHelper::cssClasses(),
            SchemaHelper::containerAttributesField(),
            SchemaHelper::inputAttributesField([
                'help' => Craft::t('formie', "Add attributes to be outputted on this field's input. Note that these attributes will be added to every radio option."),
            ]),
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineConditionsSchema(): array
    {
        return [
            SchemaHelper::enableConditionsField(),
            SchemaHelper::conditionsField(),
        ];
    }

    /**
     * @inheritdoc
     *
     * @author CraftPulse
     */
    public function defineHtmlTag(string $key, array $context = []): ?HtmlTag
    {
        $form = $context['form'] ?? null;

        if ($key === 'fieldContainer') {
            $id = $this->getHtmlId($form);

            return new HtmlTag('fieldset', [
                'class' => [
                    'fui-fieldset',
                    'fui-layout-' . $this->layout,
                ],
                'aria-describedby' => $this->instructions ? "{$id}-instructions" : null,
            ]);
        }

        if ($key === 'fieldLabel') {
            $labelPosition = $context['labelPosition'] ?? null;

            return new HtmlTag('legend', [
                'class' => [
                    'fui-legend',
                ],
                'data' => [
                    'field-label' => true,
                    'fui-sr-only' => $labelPosition instanceof HiddenPosition ? true : false,
                ],
            ]);
        }

        if ($key === 'fieldOptions') {
            return new HtmlTag('div', [
                'class' => 'fui-layout-wrap',
            ]);
        }

        if ($key === 'fieldOption') {
            return new HtmlTag('div', [
                'class' => 'fui-radio',
            ]);
        }

        if ($key === 'fieldInput') {
            $optionValue = $context['option']['value'] ?? '';

            return new HtmlTag('input', [
                'type' => 'radio',
                'id' => $this->getHtmlId($form, $optionValue),
                'class' => 'fui-input fui-radio-input',
                'name' => $this->getHtmlName(),
                'required' => $this->required ? true : null,
                'data' => [
                    'fui-id' => $this->getHtmlDataId($form, $optionValue),
                ],
            ]);
        }

        if ($key === 'fieldOptionLabel') {
            $optionValue = $context['option']['value'] ?? '';

            return new HtmlTag('label', [
                'class' => 'fui-radio-label',
                'for' => $this->getHtmlId($form, $optionValue),
            ]);
        }

        // Formie 4 has no parent implementation: it only falls back to this
        // method for tags its own renderer does not define.
        return method_exists(parent::class, 'defineHtmlTag') ? parent::defineHtmlTag($key, $context) : null;
    }

    /**
     * Formie 4: front-end input template (Formie 3 looks up `fields/client-type`).
     *
     * @author CraftPulse
     * @since  5.3.0
     */
    public static function getInputTemplatePath(): string
    {
        return 'teamleader-focus/integrations/formie/_formfields/client-type/field';
    }

    /**
     * Formie 4: apply the theme config of Formie's Radio Buttons field.
     *
     * @author CraftPulse
     * @since  5.3.0
     */
    public function themeConfigKey(): string
    {
        return 'radioButtons';
    }

    /**
     * Formie 4: form builder preview, replacing {@see getPreviewInputHtml()}.
     *
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderPreviewSchema(): array
    {
        return [
            SchemaHelper::previewChoiceList('radio', [
                'options' => [
                    ['label' => SchemaHelper::previewBind('field.companyLabel', 'Company'), 'value' => self::TYPE_COMPANY],
                    ['label' => SchemaHelper::previewBind('field.contactLabel', 'Client'), 'value' => self::TYPE_CONTACT],
                ],
                'useOptionDefaults' => false,
            ]),
        ];
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderGeneralSchema(): array
    {
        return $this->defineGeneralSchema();
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderSettingsSchema(): array
    {
        return [
            SchemaHelper::prefillQueryParam(),
            SchemaHelper::includeInEmailFieldSummariesField(),
            SchemaHelper::emailFieldSummaryValue([
                'options' => [
                    ['label' => Craft::t('formie', 'Label'), 'value' => 'label'],
                    ['label' => Craft::t('formie', 'Value'), 'value' => 'value'],
                ],
            ]),
        ];
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderValidationSchema(): array
    {
        return [
            SchemaHelper::requiredValidationMessage(),
        ];
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderAppearanceSchema(): array
    {
        return $this->defineAppearanceSchema();
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderAdvancedSchema(): array
    {
        return $this->defineAdvancedSchema();
    }

    /**
     * @author CraftPulse
     * @since  5.3.0
     */
    public function defineFormBuilderConditionsSchema(): array
    {
        return $this->defineConditionsSchema();
    }

    /**
     * Check if the submitted value indicates a B2B (company) request.
     *
     * @param mixed $value The field value
     *
     * @author CraftPulse
     */
    public function isCompanyRequest(mixed $value): bool
    {
        return $value === self::TYPE_COMPANY;
    }

    /**
     * Check if the submitted value indicates a B2C (contact-only) request.
     *
     * @param mixed $value The field value
     *
     * @author CraftPulse
     */
    public function isContactRequest(mixed $value): bool
    {
        return $value === self::TYPE_CONTACT;
    }

    // Protected Methods
    // =========================================================================

    /**
     * Formie 4: front-end tags, replacing {@see defineHtmlTag()}. Mirrors
     * Formie's Radio Buttons field so its browser module and theme config apply.
     *
     * @author CraftPulse
     * @since  5.3.0
     */
    protected function defineSlotTag(string $key, RenderContext $context): ?SlotTag
    {
        $form = $context->form;
        $optionValue = (string)($context->toArray()['option']['value'] ?? '');

        if ($key === 'fieldLayout') {
            return SlotTag::make('fieldset')
                ->core([
                    'data-formie-field-layout' => true,
                    'data-formie-radio-field-layout' => true,
                    'data-formie-layout' => $this->layout,
                    'aria-describedby' => $this->hasInstructions() ? "{$this->getHtmlId($form)}-instructions" : null,
                ])
                ->theme([
                    'class' => [
                        'formie-field-layout',
                        'formie-radio-field-layout',
                        'formie-layout-' . $this->layout,
                    ],
                ]);
        }

        if ($key === 'fieldLabel') {
            $isHiddenLabel = $context->get('labelPosition') instanceof HiddenPosition || str_contains(strtolower($this->labelPosition ?? ''), 'hidden');

            return SlotTag::make('legend')
                ->core([
                    'data-formie-label' => true,
                    'data-formie-field-label' => true,
                    'data-formie-radio-field-label' => true,
                    'data-formie-sr-only' => $isHiddenLabel,
                ])
                ->theme([
                    'class' => [
                        'formie-label',
                        'formie-field-label',
                        'formie-radio-field-label',
                        $isHiddenLabel ? 'formie-sr-only' : false,
                    ],
                ]);
        }

        if ($key === 'fieldOptions') {
            return SlotTag::make('div')
                ->core([
                    'data-formie-field-options' => true,
                    'data-formie-radio-options' => true,
                    'data-formie-layout' => $this->layout,
                ])
                ->theme([
                    'class' => [
                        'formie-field-options',
                        'formie-radio-options',
                        'formie-layout-' . $this->layout,
                    ],
                ]);
        }

        if ($key === 'fieldOption') {
            return SlotTag::make('div')
                ->core([
                    'data-formie-field-option' => true,
                    'data-formie-radio-option' => true,
                ])
                ->theme([
                    'class' => [
                        'formie-field-option',
                        'formie-radio-option',
                    ],
                ]);
        }

        if ($key === 'fieldInput') {
            return SlotTag::make('input')
                ->core(array_merge([
                    'type' => 'radio',
                    'id' => $this->getHtmlId($form, $optionValue),
                    'name' => $this->getHtmlName(),
                    'required' => true,
                    'data-formie-input' => true,
                    'data-formie-radio-input' => true,
                    'data-formie-input-id' => $this->getHtmlDataId($form, $optionValue),
                    'data-formie-input-type' => 'radio',
                ], ValidationMessagesHelper::requiredBrowserAttributes($this)))
                ->theme([
                    'class' => [
                        'formie-input',
                        'formie-radio-input',
                    ],
                ])
                ->instanceAttributes($this->getInputAttributes());
        }

        if ($key === 'fieldOptionLabel') {
            return SlotTag::make('label')
                ->core([
                    'data-formie-field-option-label' => true,
                    'data-formie-radio-option-label' => true,
                    'for' => $this->getHtmlId($form, $optionValue),
                ])
                ->theme([
                    'class' => [
                        'formie-field-option-label',
                        'formie-radio-option-label',
                    ],
                ]);
        }

        return parent::defineSlotTag($key, $context);
    }

    /**
     * Formie 4: control panel submission input, replacing {@see getInputHtml()}.
     *
     * @author CraftPulse
     * @since  5.3.0
     */
    protected function defineSubmissionHtml(mixed $value, ?ElementInterface $element, bool $inline): string
    {
        return Craft::$app->getView()->renderTemplate('formie/_formfields/radio/input', [
            'name' => $this->handle,
            'value' => $value,
            'options' => $this->getFieldOptions(),
        ]);
    }
}
