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
        $url = ToCloneUrl::fromRepoAndToken($repo_string, $token_string);
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
            [
                'git@bitbucket.org:user/repo',
                'mytoken',
                'https://x-token-auth:mytoken@bitbucket.org/user/repo.git',
            ],
            [
                'git@bitbucket.org:user/repo.git',
                'user:mytoken',
                'https://user:mytoken@bitbucket.org/user/repo.git',
            ],
            [
                'git@github.com:user/repo.git',
                'token123',
                'https://x-access-token:token123@github.com/user/repo.git',
            ],
            [
                'https://github.com/user/repo.git',
                'token123',
                'https://x-access-token:token123@github.com/user/repo.git',
            ],
            [
                'https://www.github.com/user/repo.git',
                'token123',
                'https://x-access-token:token123@github.com/user/repo.git',
            ],
            [
                'https://gitlab.com/user/repo.git',
                'token123',
                'https://oauth2:token123@gitlab.com/user/repo.git',
            ],
            [
                'https://www.gitlab.com/user/repo.git',
                'token123',
                'https://oauth2:token123@gitlab.com/user/repo.git',
            ],
            [
                'https://bitbucket.org/user/repo.git',
                'token123',
                'https://x-token-auth:token123@bitbucket.org/user/repo.git',
            ],
            [
                'https://www.bitbucket.org/user/repo.git',
                'token123',
                'https://x-token-auth:token123@bitbucket.org/user/repo.git',
            ],
            [
                'https://www.bitbucket.org/user/repo',
                'token123',
                'https://x-token-auth:token123@bitbucket.org/user/repo.git',
            ],
            [
                'https://www.bitbucket.org/user/repo.git',
                'user:token123',
                'https://user:token123@bitbucket.org/user/repo.git',
            ],
            [
                'https://gitlab.acme.com/user/repo.git',
                'mytoken',
                'https://oauth2:mytoken@gitlab.acme.com/user/repo.git',
            ],
            [
                'http://gitlab.acme.com/user/repo.git',
                'mytoken',
                'http://oauth2:mytoken@gitlab.acme.com/user/repo.git',
            ],
            [
                'https://gitlab.acme.com:9977/user/repo.git',
                'mytoken',
                'https://oauth2:mytoken@gitlab.acme.com:9977/user/repo.git',
            ],
            [
                'http://gitlab.acme.com:8877/user/repo.git',
                'mytoken',
                'http://oauth2:mytoken@gitlab.acme.com:8877/user/repo.git',
            ],
        ];
    }
}
