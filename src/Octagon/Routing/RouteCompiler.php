<?php

namespace Octagon\Routing;

use Octagon\Routing\Route;
use Octagon\Core\Register;

/**
 * Compiles a route into a CompiledRoute.
 */
class RouteCompiler
{
    const STATIC_TEXT_REGEX = '[^\{\}]+';
    const MACRO_REGEX = '\{\w+\}';
    const SEPARATOR_REGEX = '[\{\}]';

    /**
     * Compiles a route into a regex.
     *
     * @param Route $route The route to compiled.
     *
     * @return CompiledRoute The compiled route.
     */
    public static function compile(Route $route)
    {
        $compiledRegex = '';
        $authRegex = '';
        $hostRegex = '';
        $hostVariables = array();
        $pathRegex = '';
        $pathVariables = array();

        // Get the host. If the route has a defined host, let's use it.
        if (null != $host = $route->getHost()) {
            $result = self::compilePattern($route, $host);
            $hostRegex = $result['regex'];
            $hostVariables = $result['variables'];
        }
        // Use a greedy regex pattern
        else {
            $hostRegex = '[^/]+';
        }

        // Get and build the scheme regex.
        // For each scheme/port pair, format the host with a scheme and a port.
        if (null != $schemes = $route->getSchemes()) {
            foreach ($schemes as $scheme/*=>$ports*/) {
                /*
                if (isset($ports) && (is_string($ports) || is_int($ports))) {
                    $portRegex = "";
                    $portRegex .= "\\:" . preg_quote($ports);
                }
                else if (isset($ports) && is_array($ports)) {
                    $portRegex = "";
                    $portRegex .= preg_quote(":") . "(?:";
                    foreach ($ports as $port) {
                        $portRegex .= preg_quote($port);
                        if (pos($ports) !== end($ports)) {
                            $portRegex .= "|";
                        }
                    }
                    $portRegex .= ")";
                }
                else {
                    $portRegex = "";
                }
                */
                $scheme = preg_quote($scheme);
                $authRegex .= $scheme . '\://' . $hostRegex . $portRegex;
                if (pos($schemes) !== end($schemes)) {
                    $authRegex .= '|';
                }
            }

            // wrap auth regex in non-capture groups
            if ($authRegex != '') {
                $authRegex .= '(?:' . $authRegex . ')';
            }
        }
        // Because a scheme was not specified, use a greedy pattern that
        // collects the entire scheme and authorization parts of the URL.
        else {
            $authRegex .= '[^\:]+\://' . $hostRegex;
        }

        // build the path regex
        if (null != $path = $route->getPath()) {
            $result = self::compilePattern($route, $path);
            $pathRegex = $result['regex'];
            $pathVariables = $result['variables'];
        }

        // put it all together
        $compiledRegex = $authRegex . '(?:'. $pathRegex . ')';

        // return the compiled route
        return new CompiledRoute (
            $compiledRegex,
            $pathVariables,
            $hostVariables
        );
    }

    /**
     * Compiles a pattern into a regex string.
     *
     * @param Route $route     The route to use.
     * @param string $pattern  The pattern to compiled.
     *
     * @return Array Returns an array with the compiled regex, and the extracted variable identifiers if any.
     */
    public static function compilePattern(Route $route, $pattern)
    {
        /**
         * In this section, we look at the internal design of the pattern compiler.
         *
         * The pattern compiler expands tokens into regex. The pattern compiler is
         * in a sense a preprocessor. It breaks the pattern into tokens and expands
         * each token accordingly.
         *
         * # Concepts
         *
         * - A **pattern** is simply a string that can contain macros.
         *
         * - A **token** is a collection of characters that is treated as a unit.
         *
         * - "A **macro** is a rule or pattern that specifies
         *   how a certain input sequence (often a sequence of characters) should be
         *   mapped to a replacement output sequence (also often a sequence of
         *   characters) according to a defined procedure," according to Wikipedia,
         *
         * - Any text in the pattern that is not a macro is referred to as **static text**.
         *
         * - **expansion input** - TBD
         *
         * - **expansion output** - TBD
         *
         * - **Extrapolation** is the process of retrieving the expansion input
         *   and forming an expansion output. This is the glue that maps the
         *   expansion input to the expansion output.
         *
         * # Tokens
         *
         * There are two types of tokens: static-text tokens and var tokens.
         *
         * - Static-text token: A static-text token is a token that does not need
         *   to be expanded; its value is literal. When evaluated, their values
         *   do not change; hence, the reason why they are called static text.
         *
         * - Var token:  var token is a macro. This type of token is expanded.
         *
         * # The var token
         *
         * - Purpose: The var token allows the programmer to define variables in order
         *   to capture input from the URL to be passed to the application.
         *
         * - Syntax: The var token has the syntax
         *
         *   var_token = "{" id "}" ;
         *
         * - Internal design: This macro works by mapping a regex capture group to
         *   a var identifier, so that when the Router attempts to match the request
         *   URL with the compiled route, the compiled route will contain a regex
         *   capture group that is mapped to the corresponding var identifier.
         *   So, if there is a match, then the captured group will be collected
         *   and stored with its identifier.
         */

        $regex = '';           // used to store the compiled regex
        $variables = array();  // stores variables

        // We need to know how tokens are delimited in a pattern so that we can
        // know how to go about breaking down a pattern into tokens. So we ask,
        // What characters help to delimits tokens from static-text in a pattern?
        // Since macro tokens are already enclosed with the `{}` characters,
        // then these characters shall delimit static text from macros.
        $staticText = self::STATIC_TEXT_REGEX;                 // collects static-text token
        $macro = self::MACRO_REGEX;                            // collects macro token
        $separator = self::SEPARATOR_REGEX;                    // collects the separator, and helps delimit the static text from macro
        $tokenizer = "#{$macro}|{$staticText}|{$separator}#";  // splits string into tokens

        // Next, we break the pattern into tokens. With the pattern broken down
        // into tokens, we can process the pattern more easily
        preg_match_all($tokenizer, $pattern, $matches, PREG_OFFSET_CAPTURE | PREG_SET_ORDER);

        // For each token we find, we shall process it accordinly and build up
        // a regex string and a list of all variable identifiers that were
        // found in the given pattern.
        foreach ($matches as $index=>$match) {

            // Let's process a macro
            if (self::isMacro($match[0][0])) { //if (preg_match("#^{$macro}$#", $match[0][0])) {
                // Our aim at this point is to expand the macro.
                //
                // The expansion output of a macro token is extrapolated from one of
                // two places: the requirements settings of the given route,
                // or the pattern compiler itself.
                //
                // To expand the var (macro), the pattern compiler first checks for the requirements
                // settings of the given route. If no requirement is found, then the pattern
                // compiler uses its default regex.
                //
                // When checking the requirements settings of the route, the pattern compiler
                // must retrieve the var's name. The pattern compiler then pulls the requirement
                // by the var name. Then the var is expanded with the requirement value;
                // but if no requirement exists for the var, the var token is expanded
                // with the default regex.

                // First, get identifier of the variable, and store it away
                // The pattern compiler returns the identifiers of the variables
                // to the caller.
                $variable = self::extractVariableName($match[0][0]);
                $variables[] = $variable;

                // Then, get the variable's requirement settings from the given route. If this route
                // has a requirement setting for the variable, then the extrapolate the settings
                // from the route, and give the capture group the same name as the variable.
                if (!empty($requirement = $route->getRequirement($variable))) {
                    $regex .= sprintf('(?P<%s>%s)', $variable, $requirement);
                }
                // However if the route does not have a defined requirement for the variable,
                // then use the pattern compiler's default regex.
                else {
                    // The default regex instructs the regex interpreter to collect any character until
                    // it encounters the delimiter character. The delimiter is usually the first
                    // character of the next static-text token, except when the macro token is the last token.
                    //
                    // If there are more tokens ahead, then use the first character of  the next token
                    // as a delimiter; but be mindful: the next token must be a static text.
                    if (array_key_exists($index+1, $matches)) {
                        $nextToken = $matches[$index+1][0][0];
                        if (!self::isMacro($nextToken)) { //if (!preg_match("#^{$macro}$#", $nextToken)) {
                            $staticChar = $nextToken[0];
                            $regex .= sprintf('(?P<%s>[^%s]+)', $variable, $staticChar);
                        }
                        else {
                            $regex .= sprintf('(?P<%s>.+)', $variable);
                        }
                    }
                    // But if there are no more tokens ahead, then the regex can be greedy
                    else {
                        $regex .= sprintf('(?P<%s>.+)', $variable);
                    }
                }
            }
            // The token is not a macro; therefore it must be static text. The expansion output
            // of a static-text token is extrapolated from the static-text token itself.
            // Not much expansion is done to static text. The static-text token is only evaluated
            // for escapes. The evaluation result is returned as the expansion output.
            else {
                $staticText = $match[0][0];
                $staticText = preg_quote($staticText);
                $regex .= $staticText;
            }

        }

        return array (
            'regex' => $regex,
            'variables' => $variables
        );
    }

    /**
     * Checks if subject is a macro.
     *
     * This method implements a finite-state machine that parses a string
     * and checks if it is a macro.
     *
     * @param string $subject The string to recognize.
     *
     * @return bool Returns TRUE if string is a macro; returns FALSE otherwise.
     */
    public static function isMacro($subject)
    {
        $len = strlen($subject);
        $i = 0;
        $current_state = 0;
        $next_state = 2;
        while (true) {
            $current_state = $next_state;
            switch ($current_state) {
                case 0: // false
                    return false;
                    break;
                case 1: // true
                    return true;
                    break;
                case 2: // accept left curly bracket
                    if ($subject[$i] === '{') {
                        $next_state = 3;
                    }
                    else {
                        $next_state = 0;
                    }
                    break;
                case 3: // accept a word (i.e. `[A-Za-z0-9_]`)
                    if (ord($subject[$i]) >= ord('a') && ord($subject[$i]) <= ord('z')) {
                        $next_state = $current_state;
                    }
                    else if (ord($subject[$i]) >= ord('A') && ord($subject[$i]) <= ord('Z')) {
                        $next_state = $current_state;
                    }
                    else if (ord($subject[$i]) >= ord('0') && ord($subject[$i]) <= ord('9')) {
                        $next_state = $current_state;
                    }
                    else if ($subject[$i] === '_') {
                        $next_state = $current_state;
                    }
                    else if ($subject[$i] === '}') {
                        $next_state = 4;
                    }
                    else {
                        $next_state = 0;
                    }
                    break;
                case 4: // test for end of string
                    if (!isset($subject[$i])) {
                        $next_state = 1;
                    }
                    else {
                        $next_state = 0;
                    }
                    break;
            }
            $i++;
        }
    }

    /**
     * Substitutes placeholders with values.
     *
     * @param string $pattern The pattern whoses variables are to be replaced with values.
     * @param array  $args    The replacement values.
     *
     * @return string Returns a string with substituted values. If no value is
     *   supplied in $args, then the string is returned as is.
     */
    public static function substitutePatternArgs($pattern, $args = array())
    {
        // Check the given pattern for variables. For each variable found,
        // extract the variable name and get substitue value from $args
        // by the variable name.
        $macro = self::MACRO_REGEX;
        return preg_replace_callback("#{$macro}#", function($matches) use ($args) {
                $variable = self::extractVariableName($matches[0]);
                return isset($args[$variable]) ? $args[$variable] : $matches[0] ;
            },
            $pattern
        );
    }

    /**
     * Extract the variable name from the macro token
     *
     * @param string $token The macro token from which to extract variable name
     *
     * @return string Returns the extracted name.
     */
    public static function extractVariableName($token) {
        // Remove the curly brackets to get the variable identifier
        return substr($token, 1, -1);
    }

}
