<?php

namespace App\Http\Requests\Catalog;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

/**
 * ProductVariantRequest
 *
 * Validates Catalog-related input payloads.
 *
 * @author Abdul Wadood
 */
class ProductVariantRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'sku' => ['required', 'string', 'max:255', 'unique:product_variants,sku,'.($this->variant->id ?? 'NULL').',id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'default' => ['boolean'],
            'active' => ['boolean'],

            'attributes' => ['sometimes', 'array'],
            'attributes.*.attribute_id' => ['required', 'integer', 'exists:attributes,id', 'distinct'],
            'attributes.*.option_id' => [
                'nullable',
                'integer',
                'exists:attribute_options,id',
                'required_without:attributes.*.value',
            ],
            'attributes.*.value' => [
                'nullable',
                'string',
                'max:255',
                'required_without:attributes.*.option_id',
            ],
        ];
    }

    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $v) {
            $attrs = $this->input('attributes', []);
            foreach ($attrs as $i => $row) {
                $opt = $row['option_id'] ?? null;
                $val = $row['value'] ?? null;

                if (is_null($opt) && (is_null($val) || trim((string) $val) === '')) {
                    $v->errors()->add("attributes.$i", __('catalog.option_or_value'));
                }
            }
        });
    }
}
