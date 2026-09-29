<?php

namespace App\Models;

use Database\Factories\EndpointFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Response;
use Illuminate\Support\Collection;

class Endpoint extends Model
{
    /** @use HasFactory<EndpointFactory> */
    use HasFactory;

    public const METHODS = [
        'GET' => 'GET',
        'POST' => 'POST',
        'PUT' => 'PUT',
        'PATCH' => 'PATCH',
        'DELETE' => 'DELETE',
    ];

    public const CONTENT_TYPES = [
        'application/json' => 'JSON',
        'text/html' => 'HTML',
        'text/plain' => 'Plain Text',
        'application/xml' => 'XML',
        'text/csv' => 'CSV',
    ];

    protected $fillable = [
        'method',
        'path',
        'status_code',
        'content_type',
        'body',
        'headers',
        'note',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'status_code' => 'integer',
            'headers' => 'array',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Normalize a path so stored paths and incoming request paths compare equally.
     */
    public static function normalizePath(string $path): string
    {
        $path = '/'.trim($path);
        $path = preg_replace('#/{2,}#', '/', $path) ?? $path;

        if ($path !== '/') {
            $path = rtrim($path, '/');
        }

        return $path;
    }

    public function setPathAttribute(?string $value): void
    {
        $this->attributes['path'] = self::normalizePath($value ?? '');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Resolve the endpoint registered for the given request, if any.
     */
    public static function findForRequest(string $method, string $path): ?self
    {
        return self::query()
            ->active()
            ->where('path', self::normalizePath($path))
            ->where('method', strtoupper($method))
            ->first();
    }

    /**
     * The methods registered for a path, used to answer with a 405 when none match.
     */
    public static function allowedMethodsForPath(string $path): Collection
    {
        return self::query()
            ->active()
            ->where('path', self::normalizePath($path))
            ->pluck('method');
    }

    public function toResponse(): Response
    {
        $response = response($this->body ?? '', $this->status_code);

        $response->headers->set('Content-Type', $this->content_type);

        foreach ($this->headers ?? [] as $name => $value) {
            $response->headers->set($name, $value);
        }

        return $response;
    }
}
