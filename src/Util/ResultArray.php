<?php

namespace Abc\Job\Util;

use Abc\Job\Result;

class ResultArray
{
    /**
     * @param string $json
     * @return Result[]
     */
    public static function fromJson(string $json): array
    {
        $results = [];
        foreach (json_decode($json, true) as $resultData) {
            $results[] = Result::fromArray($resultData);
        }

        return $results;
    }

    /**
     * @param $results Result[]
     * @return string $json
     */
    public static function toJson(array $results): string
    {
        $data = [];
        foreach ($results as $result) {
            $data[] = $result->toArray();
        }

        return json_encode($data);
    }
}
