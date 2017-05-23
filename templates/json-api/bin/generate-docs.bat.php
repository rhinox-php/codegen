pushd %~dp0\..
mkdir docs\output
node bin\generate-docs.js
call .\node_modules\.bin\bootprint openapi docs\output\api.yml docs\output
popd
