<?php

namespace Ebolution\Core\Domain\Mappers;

/**
 * This array mapper uses rules to convert an array into another array.
 *
 * Taken into consideration the order in which rules are applied. Each rule
 * will apply to the result of applying previous rules (eg. If you rename
 * 'products' as 'items' and then you want to rename 'products.*.id_product',
 * remember that this is currently 'items.*.id_product').
 *
 * Rules are applied top to bottom on each category:
 *
 *  1st Transform rules
 *  2nd Unset rules
 *  3rd Rename rule
 *
 * Rules are defined on derived classes. Transform rules must be set on the initialize() method.
 */
abstract class AbstractArrayMapper
{
    /**
     * @var array Rules to unset elements
     * Sample:
     * [
     *      'internal_secret' => true,      // Regular top level field
     *      'valid_field' => false,         // Do not unset (irrelevant rule!)
     *      'items' => [
     *          '*' => [                    // Array of elements without keys ($i=0..N)
     *              'cost' => true,         // Regular field ($data['items'][$i]['cost'])
     *          ],
     *      ],
     *      'address' => [                  // Compound object
     *          'mobile' => true,           // Regular field ($data['address']['mobile'])
     *      ],
     * ]
     */
    protected array $unsetRules = [];

    /**
     * @var array Rules to rename elements (current_name => new_name)
     * Sample:
     * [
     *      'id_product' => 'id',                       // Regular top level field
     *      'items' => [
     *          '*' => [                                // Array of elements without keys ($i=0..N)
     *              'total_ti' => 'total_tax_incl',     // Regular field ($data['items'][$i]['total_ti'])
     *          ],
     *      ],
     *      'address' => [                              // Compound object
     *          'given_name' => 'first_name'            // Regular field ($data['address']['given_name'])
     *          'family_name' => 'last_name'
     *          'surname' => 'last_name',               // Alias
     *      ],
     * ]
     */
    protected array $renameRules = [];

    /**
     * @var array Rules to transform elements
     *
     * Sample:
     * $this->transformRules['products']['*']['amount'] = function(?string &$data) {
     *       $data = MoneyFormatter::formatLocale($data);
     *  };
     *
     * Can use the same syntax as $unsetRules and $renameRules for compound objects and arrays.
     */
    protected array $transformRules = [];

    protected array $result;

    protected abstract function initialize(): void;

    public function __invoke(array $data): array
    {
        $this->result = $data;

        $this->initialize();
        $this->transformElements();
        $this->unsetElements();
        $this->renameElements();

        return $this->result;
    }

    private function unsetElements(): void
    {
        $this->unsetElementChildren($this->result, $this->unsetRules);
    }

    private function unsetElementChildren(mixed &$data, array $rules): void
    {
        foreach ($rules as $element => $inner_rules) {
            if ( array_key_exists($element,  $data) ) {
                $this->unsetElement($data, $element, $inner_rules);
            }
        }
    }

    private function unsetElement(mixed &$data, string $element, bool|array $rules): void
    {
        if ( is_array($rules) ) {
            foreach ($rules as $inner_element => $inner_rules) {
                if ( $inner_element === '*' ) {
                    foreach ($data[$element] as &$inner_data) {
                        $this->unsetElementChildren($inner_data, $inner_rules);
                    }
                } elseif ( array_key_exists($inner_element, $data[$element]) ) {
                    $this->unsetElement($data[$element], $inner_element, $inner_rules);
                }
            }
        } else {
            if ( $rules ) {
                unset($data[$element]);
            }
        }
    }

    private function renameElements(): void
    {
        $this->renameElementChildren($this->result, $this->renameRules);
    }

    private function renameElementChildren(mixed &$data, array $rules): void
    {
        foreach ($rules as $element => $inner_rules) {
            if ( array_key_exists($element, $data) ) {
                $this->renameElement($data, $element, $inner_rules);
            }
        }
    }

    private function renameElement(array &$data, string $element, string|array $rules): void
    {
        if ( is_array($rules) ) {
            foreach ($rules as $inner_element => $inner_rules) {
                if ( $inner_element === '*' ) {
                    foreach ($data[$element] as &$inner_data) {
                        $this->renameElementChildren($inner_data, $inner_rules);
                    }
                } elseif ( array_key_exists($inner_element, $data[$element]) ) {
                    $this->renameElement($data[$element], $inner_element, $inner_rules);
                }
            }
        } else {
            $new_name = $rules;
            $data[$new_name] = $data[$element];
            unset($data[$element]);
        }
    }

    private function transformElements(): void
    {
        $this->transformElementChildren($this->result, $this->transformRules);
    }

    private function transformElementChildren(mixed &$data, array $rules): void
    {
        foreach ($rules as $element => $inner_rules) {
            if ( array_key_exists($element, $data) ) {
                $this->transformElement($data, $element, $inner_rules);
            }
        }
    }

    private function transformElement(mixed &$data, string $element, mixed $rules): void
    {
        if ( is_array($rules) ) {
            foreach ($rules as $inner_element => $inner_rules) {
                if ( $inner_element === '*' ) {
                    foreach ($data[$element] as &$inner_data) {
                        $this->transformElementChildren($inner_data, $inner_rules);
                    }
                } elseif ( array_key_exists($inner_element, $data[$element]) ) {
                    $this->transformElement($data[$element], $inner_element, $inner_rules);
                }
            }
        } else {
            $closure = $rules;
            if ( is_callable($closure) ) {
                $closure($data[$element]);
            }
        }
    }
}
