<?php

namespace Rapid\Voyager\Remote;

use Rapid\Voyager\VoyagerFactory;

class RequestFormatter
{

    public static function encode(VoyagerFactory $voyager, string $type, array $args, string $content)
    {
        $args = json_encode($args);
        $request = $type . ':' . strlen($args) . ':' . strlen($content) . ':' . $args . $content;

        $sumCheck = $voyager->hash($request);

        return $request . $sumCheck;
    }

    public static function decode(VoyagerFactory $voyager, string $input, ?string &$type = null, ?array &$args = null, ?string &$content = null)
    {
        @[$type, $argsLength, $contentLength, $argsAndContent] = explode(':', $input, 4);

        if (
            $type &&
            is_numeric($argsLength) &&
            is_numeric($contentLength) &&
            $argsLength >= 0 &&
            $contentLength >= 0 &&
            $argsAndContent &&
            $argsLength + $contentLength <= strlen($argsAndContent)
        )
        {
            $requestSumCheck = substr($argsAndContent, $argsLength + $contentLength);
            $sumCheck = $voyager->hash(substr($input, 0, -strlen($requestSumCheck)));

            if ($requestSumCheck != $sumCheck)
            {
                return false;
            }

            $argsJson = substr($argsAndContent, 0, $argsLength);
            if (is_array($argsTest = @json_decode($argsJson, true)))
            {
                $args = $argsTest;
            }
            else return false;

            $content = substr($argsAndContent, $argsLength, $contentLength);

            return true;
        }

        return false;
    }

}