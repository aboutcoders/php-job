<?php

namespace Abc\Job;

use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     description="A job to be processed"
 * )
 */
class Job
{
    /**
     * @OA\Property(
     *     description="The job type",
     *     enum={"Job", "Batch", "Sequence"},
     * )
     *
     * @var string
     */
    private $type;

    /**
     * @OA\Property(
     *     description="The job name"
     * )
     *
     * @var string
     */
    private $name;

    /**
     * @OA\Property(
     *     description="The job input"
     * )
     *
     * @var string
     */
    private $input;

    /**
     * @OA\Property(
     *     type="array",
     *         @OA\Items(ref="#/components/schemas/Job")
     *     )
     * )
     *
     * @var Job[]
     */
    private $children = [];

    /**
     * @OA\Property(
     *     description="Whether a job in a Sequence or Batch may fail"
     * )
     *
     * @var bool
     */
    private $allowFailure;

    /**
     * @OA\Property(
     *     format="uuid",
     *     description="An external identifier of the job"
     * )
     *
     * @var string
     */
    private $externalId;

    public function __construct(
        Type $type,
        string $name = null,
        string $input = null,
        array $children = [],
        bool $allowFailure = false,
        string $externalId = null
    ) {
        if (Type::JOB() == $type && null == $name) {
            throw new \InvalidArgumentException(sprintf('Type %s expects argument $name', $type));
        }

        if (Type::JOB() == $type && 0 < count($children)) {
            throw new \InvalidArgumentException(sprintf('Type %s cannot have children', $type));
        }

        if (Type::JOB() != $type && 1 >= count($children)) {
            throw new \InvalidArgumentException(sprintf('Type %s expects at least two children, given has %s', $type, count($children) == 0 ? 'zero' : 'one'));
        }

        foreach ($children as $child) {
            if (! is_object($child) || ! is_a($child, self::class)) {
                throw new \InvalidArgumentException(sprintf('Expected %s got %s', self::class, is_object($child) ? gettype($child) : get_class($child)));
            }
            $this->children[] = $child;
        }

        $this->type = $type;
        $this->name = $name;
        $this->input = $input;
        $this->allowFailure = $allowFailure;
        $this->externalId = $externalId;
    }

    public function getType(): Type
    {
        return new Type($this->type);
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function getInput(): ?string
    {
        return $this->input;
    }

    public function setInput(?string $input)
    {
        $this->input = $input;
    }

    /**
     * @return Job[]
     */
    public function getChildren(): array
    {
        return $this->children;
    }

    public function isAllowFailure(): bool
    {
        return $this->allowFailure;
    }

    public function getExternalId(): ?string
    {
        return $this->externalId;
    }

    public function setExternalId(?string $externalId)
    {
        $this->externalId = $externalId;
    }

    public function toArray(): array
    {
        return [
            'type' => (string) $this->getType(),
            'name' => $this->getName(),
            'input' => $this->getInput(),
            'allowFailure' => $this->isAllowFailure(),
            'externalId' => $this->getExternalId(),
            'children' => array_map(function (Job $child) {
                return $child->toArray();
            }, $this->getChildren()),
        ];
    }

    public static function fromArray(array $data): Job
    {
        foreach (['type', 'name'] as $property) {
            if (! isset($data[$property])) {
                throw new \InvalidArgumentException(sprintf('The property "%s" must be set', $property));
            }
        }

        $children = [];
        if (isset($data['children'])) {
            foreach ($data['children'] as $childArray) {
                $children[] = static::fromArray($childArray);
            }
        }

        return new static(new Type($data['type']), $data['name'] ?? null, $data['input'] ?? null, $children, $data['allowFailure'] ?? false, $data['externalId'] ?? null);
    }

    public function toJson(): string
    {
        return json_encode((object) $this->toArray());
    }

    public static function fromJson(string $json)
    {
        $data = @json_decode($json, true);

        if (null === $data) {
            throw new \InvalidArgumentException('Invalid json');
        }

        return static::fromArray($data);
    }
}
