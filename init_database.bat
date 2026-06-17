@echo off
chcp 65001 >nul
title WRQTestMall 数据库一键初始化

echo ========================================
echo   WRQTestMall 数据库初始化工具
echo ========================================
echo.
echo   连接信息:
echo     地址: 127.0.0.1:3306
echo     用户: root
echo     库名: wrqtestmall
echo.

:: 尝试查找 mysql 可执行文件
set "MYSQL_CMD="

:: 常见安装路径
if exist "C:\xampp\mysql\bin\mysql.exe" (
    set "MYSQL_CMD=C:\xampp\mysql\bin\mysql.exe"
    goto :found
)
if exist "C:\phpstudy_pro\Extensions\MySQL8.0.12\bin\mysql.exe" (
    set "MYSQL_CMD=C:\phpstudy_pro\Extensions\MySQL8.0.12\bin\mysql.exe"
    goto :found
)
if exist "C:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe" (
    set "MYSQL_CMD=C:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe"
    goto :found
)
if exist "D:\phpstudy_pro\Extensions\MySQL8.0.12\bin\mysql.exe" (
    set "MYSQL_CMD=D:\phpstudy_pro\Extensions\MySQL8.0.12\bin\mysql.exe"
    goto :found
)
if exist "D:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe" (
    set "MYSQL_CMD=D:\phpstudy_pro\Extensions\MySQL5.7.26\bin\mysql.exe"
    goto :found
)
if exist "C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe" (
    set "MYSQL_CMD=C:\Program Files\MySQL\MySQL Server 8.0\bin\mysql.exe"
    goto :found
)
if exist "C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe" (
    set "MYSQL_CMD=C:\Program Files\MySQL\MySQL Server 5.7\bin\mysql.exe"
    goto :found
)

:: 尝试 PATH 中的 mysql
where mysql >nul 2>&1
if %errorlevel%==0 (
    set "MYSQL_CMD=mysql"
    goto :found
)

echo [错误] 未找到 mysql 命令行工具！
echo.
echo 请确保以下任一条件满足：
echo   1. MySQL 已安装且 bin 目录在 PATH 环境变量中
echo   2. 使用 XAMPP / phpStudy 等集成环境
echo.
set /p "MYSQL_CMD=请手动输入 mysql.exe 的完整路径: "
if not exist "%MYSQL_CMD%" (
    echo 路径无效，退出。
    pause
    exit /b 1
)

:found
echo [信息] 使用 MySQL 客户端: %MYSQL_CMD%
echo.

:: 获取当前脚本所在目录
set "SCRIPT_DIR=%~dp0"
set "SQL_FILE=%SCRIPT_DIR%sql\wrqtestmall.sql"

if not exist "%SQL_FILE%" (
    echo [错误] 找不到 SQL 文件: %SQL_FILE%
    pause
    exit /b 1
)

echo [步骤 1/2] 测试数据库连接...
"%MYSQL_CMD%" -h 127.0.0.1 -P 3306 -u root -p123456 -e "SELECT 1;" >nul 2>&1
if %errorlevel% neq 0 (
    echo [错误] 数据库连接失败！请检查：
    echo   - MySQL 服务是否已启动
    echo   - 端口是否为 3306
    echo   - 密码是否为 123456
    pause
    exit /b 1
)
echo [成功] 数据库连接正常

echo.
echo [步骤 2/2] 导入数据库脚本...
"%MYSQL_CMD%" -h 127.0.0.1 -P 3306 -u root -p123456 --default-character-set=utf8mb4 < "%SQL_FILE%"

if %errorlevel%==0 (
    echo.
    echo ========================================
    echo   初始化完成！
    echo ========================================
    echo.
    echo   数据库: wrqtestmall
    echo   管理员: admin / admin123
    echo   管理员2: manager / manager888
    echo   用户(20个): 密码统一 123456
    echo     zhangwei, lina, wangfang, liuyang, chenmin,
    echo     zhaolei, sunli, zhoujie, wuqiang, zhengxue,
    echo     huanghai, xuemei, malong, hejing, guofeng,
    echo     tangyan, songbo, yanli, fengchao, caiyun
    echo   商品: 15 条  订单: 18 条
    echo.
    echo   前台入口: http://localhost:8080/wrq-test-mall/index.php
    echo   后台入口: http://localhost:8080/wrq-test-mall/admin/login.php
    echo.
    echo   启动方法: cd wrq-test-mall ^&^& php -S 0.0.0.0:8080
    echo ========================================
) else (
    echo.
    echo [错误] 导入过程中出现错误，请检查上方输出。
)

echo.
pause
