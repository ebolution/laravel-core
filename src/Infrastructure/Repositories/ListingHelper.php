<?php

namespace Ebolution\Core\Infrastructure\Repositories;

use Illuminate\Database\Query\Builder as QueryBuilder;
use Illuminate\Database\Eloquent\Builder as EloquentBuilder;

use Carbon\Carbon;
use Illuminate\Http\Request;

/**
 * Expected query parameters:
 *
 * Paginated:
 * - page
 * - limit
 *
 * Between dates:
 * - fromDate
 * - toDate
 */
final class ListingHelper
{
    use OutputMappings;

    private const DEFAULT_PAGE_SIZE = 50;

    private const VALID_ITEMS = ['data', 'links', 'meta'];

    private ?array $data = null;
    private ?int $limit = null;
    private ?int $page = null;
    private ?string $fromDate = null;
    private ?string $toDate = null;
    private ?int $offset = null;
    private int $totalItems;
    private int $lastPage;
    private Request $request;
    private array $parameters = [
        'page' => 'page',
        'limit' => 'limit',
        'fromDate' => 'fromDate',
        'toDate' => 'toDate',
    ];

    public function __construct(
        private QueryBuilder|EloquentBuilder $query
    ) {
        $this->request = request();
    }

    public function paginated(array $parameters = ['page', 'limit']): ListingHelper
    {
        $this->setPaginationParameters($parameters);

        $this->totalItems = $this->query->count();
        $this->lastPage = $this->limit ? ceil($this->totalItems / $this->limit) : 1;

        if ( !empty($this->limit) or !empty($this->page) ) {
            $this->limit = $this->limit ?? self::DEFAULT_PAGE_SIZE;
            $this->limit = $this->limit <= 0 ? self::DEFAULT_PAGE_SIZE : $this->limit;

            $this->page = $this->page ?? 1;
            $this->page = $this->page <= 0 ? 1 : $this->page;

            $this->offset = ($this->page - 1) * $this->limit;

            $this->query = $this->query->offset($this->offset)->limit($this->limit);
        }

        return $this;
    }

    private function setPaginationParameters(array $parameters): void
    {
        $this->page = $this->request->input($parameters[0]) ?? null;
        $this->limit = $this->request->input($parameters[1]) ?? null;

        $this->parameters['page'] = $parameters[0];
        $this->parameters['limit'] = $parameters[1];
    }

    public function betweenDates(string $column = 'created_at', array $parameters = ['fromDate', 'toDate']): ListingHelper
    {
        $this->setBetweenDatesParameters($parameters);

        if ( !empty($this->fromDate) or !empty($this->toDate) ) {
            $from = $this->fromDate ? new Carbon($this->fromDate) : Carbon::minValue();
            $to = $this->toDate ? new Carbon($this->toDate) : Carbon::maxValue();

            $this->query = $this->query->whereBetween($column, [$from, $to]);
        }

        return $this;
    }

    private function setBetweenDatesParameters(array $parameters): void
    {
        $this->fromDate = $this->request->input($parameters[0]) ?? null;
        $this->toDate = $this->request->input($parameters[1]) ?? null;

        $this->parameters['fromDate'] = $parameters[0];
        $this->parameters['toDate'] = $parameters[1];
    }

    public function map(string $mapper, ?string $configKey = null): ListingHelper
    {
        if ( $configKey ) {
            $this->setMappers($configKey);
        }

        $this->data = $this->mapList($mapper, $this->materialize());

        return $this;
    }

    public function get(string|array $items = 'data'): array
    {
        $single_item = false;
        $result = [];

        if ( !is_array($items) ) {
            $items = [ $items ];
            $single_item = true;
        }

        $this->materialize();

        foreach (self::VALID_ITEMS as $item) {
            $method = 'get' . ucfirst($item);
            if ( in_array($item, $items) ){
                if ($single_item) {
                    $result = $this->$method();
                } else {
                    $result[$item] = $this->$method();
                }
            }
        }

        return $result;
    }

    private function materialize(): array
    {
        if ( !$this->data ) {
            $query_data = $this->query->get();
            $this->data = $query_data ? $query_data->toArray() : [];
        }

        return $this->data;
    }

    private function getData(): array
    {
        return $this->data;
    }

    private function getLinks(): array
    {
        $links = [];
        $query_strings = [
            'first' => [],
            'last' => [],
            'next' => [],
            'prev' => [],
        ];
        $params = [
            'page' => $this->page ?? 1,
            'limit' => $this->limit ?? self::DEFAULT_PAGE_SIZE,
            'fromDate' => $this->fromDate,
            'toDate' => $this->toDate,
        ];
        $url = $this->request->url();

        foreach ($this->parameters as $std_param => $current_param) {
            if ($params[$std_param]) {
                foreach ($query_strings as $case => $query_parameters) {
                    $value = $this->computeParam($current_param, $params[$std_param], $case);
                    if ($value) {
                        $query_strings[$case][] = $current_param . '=' . $value;
                    } else {
                        unset($query_strings[$case]);
                    }
                }
            }
        }

        foreach ($query_strings as $case => $query_parameters) {
            $links[$case] = $url . '?' . implode('&', $query_parameters);
        }

        return $links;
    }

    private function computeParam(string $param, string $value, string $case)
    {
        if ( $param === $this->parameters['page'] ) {
            $page = $this->page ?? 1;
            return match ($case) {
                'first' => 1,
                'last' => $this->lastPage,
                'prev' => ($page > 1 and $page <= $this->lastPage) ? $page - 1 : null,
                'next' => $page < $this->lastPage ? $page + 1 : null,
                default => $value
            };
        }

        return $value;
    }

    private function getMeta(): array
    {
        $page = $this->page ?? 1;
        $limit = $this->limit ?? self::DEFAULT_PAGE_SIZE;
        $offset = ($this->offset ?? 0) + 1;

        return [
            'path' => $this->request->path(),
            'current_page' => $page,
            'from' => $offset <= $this->totalItems ? $offset : null,
            'to' => $offset <= $this->totalItems ? $offset + sizeof($this->data) : null,
            'last_page' => $this->lastPage,
            'per_page' => $limit,
            'total' => $this->totalItems,
        ];
    }
}
