@ECHO OFF
setlocal DISABLEDELAYEDEXPANSION
SET BIN_TARGET=%~dp0/../rgooding/protobuf-php/protoc-gen-php.bat
call "%BIN_TARGET%" %*
