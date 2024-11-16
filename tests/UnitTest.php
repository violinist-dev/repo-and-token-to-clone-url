<?php

namespace Violinist\Tests\RepoAndTokenToCloneUrl;

use PHPUnit\Framework\TestCase;
use Violinist\RepoAndTokenToCloneUrl\ToCloneUrl;

class UnitTest extends TestCase
{
    /**
     * Test all variations we support.
     *
     * @dataProvider provideAllCases
     */
    public function testAllCasesInOne(string $repo_string, string $token_string, string $expected_clone_string) : void
    {
        $url = ToCloneUrl::getCloneUrl($repo_string, $token_string);
        self::assertEquals($expected_clone_string, $url);
    }

    /**
     * A data provider for all cases.
     */
    public static function provideAllCases() : array
    {
        return [
            [
                'git@bitbucket.org:user/repo.git',
                'mytoken',
                'https://x-token-auth:mytoken@bitbucket.org/user/repo.git',
            ],
        ];
    }
}
