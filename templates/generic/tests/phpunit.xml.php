<phpunit bootstrap="../include.php">
    <testsuites>
        <testsuite name="Test">
            <directory>Model</directory>
        </testsuite>
    </testsuites>
    <logging>
        <log type="coverage-php" target="../reports/code-coverage.php" />
        <log type="coverage-html" target="../reports/code-coverage" />
        <log type="testdox-html" target="../reports/testdox.html" />
    </logging>
    <filter>
        <whitelist>
            <directory>../src</directory>
        </whitelist>
    </filter>
</phpunit>
