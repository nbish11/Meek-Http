<?php

namespace Meek\Http\Uri;

class Parser
{
    const COMPONENTS_REGEXP = '
        ((?<scheme>[^:/?\#]+):)?
        (//(?<authority>[^/?\#]*))?
        (?<path>[^?\#]*)
        (\?(?<query>[^\#]*))?
        (\#(?<fragment>.*))?
    ';

    private static $components = [
        'scheme' => null,
        'authority' => null,
        'userinfo' => null,
        'host' => null,
        'port' => null,
        'path' => '',
        'query' => null,
        'fragment' => null
    ];

    /**
     * Method for extracting the 5 main components of a URI.
     *
     * A `null` value indicates that that component of the URI
     * was not set. An empty string for the value indicates that
     * that component was set, but empty.
     *
     * @see http://tools.ietf.org/html/rfc3986#page-51
     *
     * @param  string $uri The URI to parse.
     * @return array       An array containing the following URI
     *                     components: scheme, authority, path,
     *                     query and fragment.
     */
    public function getComponents($uri)
    {
        // BUG?: authority returns empty string instead of null, test with "uri:urn:80"
        preg_match('~^' . self::COMPONENTS_REGEXP . '~x', $uri, $matches);

        $this->filterMatches($matches);

        return array_merge(self::$components, $matches);
    }

    //
    //
    /**
     * Method for extracting the 7 main components of a URI.
     *
     * A `null` value indicates that that component of the URI
     * was not set. An empty string for the value indicates that
     * that component was set, but empty.
     *
     * @see https://gist.github.com/kpobococ/92f120c6c4a9a52b84e3
     * @see https://github.com/Riimu/Kit-UrlParser/blob/master/src/UriPattern.php
     *
     * @param  string $uri The URI to parse.
     * @return array       An array containing the following URI
     *                     components: scheme, userinfo, host,
     *                     port, path, query and fragment.
     */
    public function parse($uri)
    {
        $alpha = 'A-Za-z';
        $digit = '0-9';
        $hex = $digit . 'A-Fa-f';
        $unreserved = "$alpha$digit\\-._~";
        $delimiters = "!$&'()*+,;=";
        $utf8 = '\\x80-\\xFF';
        $octet = "(?:[$digit]|[1-9][$digit]|1[$digit]{2}|2[0-4]$digit|25[0-5])";
        $ipv4address = "(?>$octet\\.$octet\\.$octet\\.$octet)";
        $encoded = "%[$hex]{2}";
        $h16 = "[$hex]{1,4}";
        $ls32 = "(?:$h16:$h16|$ipv4address)";
        $data = "[$unreserved$delimiters:@$utf8]++|$encoded";

        // Defining the scheme
        $scheme = "(?'scheme'(?>[$alpha][$alpha$digit+\\-.]*+))";

        // Defining the authority
        $ipv6address = "(?'IPv6address'" .
            "(?:(?:$h16:){6}$ls32)|" .
            "(?:::(?:$h16:){5}$ls32)|" .
            "(?:(?:$h16)?::(?:$h16:){4}$ls32)|" .
            "(?:(?:(?:$h16:){0,1}$h16)?::(?:$h16:){3}$ls32)|" .
            "(?:(?:(?:$h16:){0,2}$h16)?::(?:$h16:){2}$ls32)|" .
            "(?:(?:(?:$h16:){0,3}$h16)?::$h16:$ls32)|" .
            "(?:(?:(?:$h16:){0,4}$h16)?::$ls32)|" .
            "(?:(?:(?:$h16:){0,5}$h16)?::$h16)|" .
            "(?:(?:(?:$h16:){0,6}$h16)?::))";
        $regularName = "(?'reg_name'(?>(?:[$unreserved$delimiters$utf8]++|$encoded)*))";
        $ipvFuture = "(?'IPvFuture'v[$hex]++\\.[$unreserved$delimiters:]++)";
        $ipLiteral = "(?'IP_literal'\\[(?>$ipv6address|$ipvFuture)\\])";
        $port = "(?'port'(?>[$digit]*+))";
        $host = "(?'host'$ipLiteral|(?'IPv4address'$ipv4address)|$regularName)";
        $userInfo = "(?'userinfo'(?>(?:[$unreserved$delimiters:$utf8]++|$encoded)*))";
        $authority = "(?'authority'(?:$userInfo@)?$host(?::$port)?)";

        // Defining the path
        $segment = "(?>(?:$data)*)";
        $segmentNotEmpty = "(?>(?:$data)+)";
        $segmentNoScheme = "(?>([$unreserved$delimiters@$utf8]++|$encoded)+)";
        $pathAbsoluteEmpty = "(?'path_abempty'(?:/$segment)*)";
        $pathAbsolute = "(?'path_absolute'/(?:$segmentNotEmpty(?:/$segment)*)?)";
        $pathNoScheme = "(?'path_noscheme'$segmentNoScheme(?:/$segment)*)";
        $pathRootless = "(?'path_rootless'$segmentNotEmpty(?:/$segment)*)";
        $pathEmpty = "(?'path_empty')";

        // Defining other parts
        $query = "(?'query'(?>(?:$data|[/?])*))";
        $fragment = "(?'fragment'(?>(?:$data|[/?])*))";
        $absolutePath = "(?'hier_part'//$authority$pathAbsoluteEmpty|$pathAbsolute|$pathRootless|$pathEmpty)";
        $relativePath = "(?'relative_part'//$authority$pathAbsoluteEmpty|$pathAbsolute|$pathNoScheme|$pathEmpty)";

        preg_match("#^$scheme:$absolutePath(?:\\?$query)?(?:\\#$fragment)?$#", $uri, $matches);

        $this->filterMatches($matches);

        return $matches;
    }

    private function filterMatches(&$components)
    {
        foreach ($components as $key => &$value) {
            if (is_numeric($key)) {
                unset($components[$key]);
            }
        }
    }
}
