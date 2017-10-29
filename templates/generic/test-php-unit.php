<phpunit bootstrap="../vendor/autoload.php">
    <testsuites>
        <testsuite name="Test">
            <directory>Model</directory>
        </testsuite>
    </testsuites>
    <logging>
        <log type="coverage-html" target="../reports/report" />
        <log type="testdox-html" target="../reports/testdox.html" />
    </logging>
    <filter>
        <whitelist>
            <directory>../src</directory>
        </whitelist>
    </filter>
</phpunit>
