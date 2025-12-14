# ใช้ Base Image เป็น PHP 8.2 พร้อม Apache
FROM php:8.2-apache

# ติดตั้ง System Dependencies และ Python 3
RUN apt-get update && apt-get install -y \
    python3 \
    python3-pip \
    python3-venv \
    git \
    unzip \
    && rm -rf /var/lib/apt/lists/*

# ติดตั้ง PHP Extensions ที่จำเป็น (mysqli สำหรับเชื่อมต่อ Database)
RUN docker-php-ext-install mysqli pdo pdo_mysql

# เปิดใช้งาน Apache mod_rewrite (เผื่อใช้ในอนาคต)
RUN a2enmod rewrite

# ตั้งค่า Python Virtual Environment
ENV VIRTUAL_ENV=/opt/venv
RUN python3 -m venv $VIRTUAL_ENV
ENV PATH="$VIRTUAL_ENV/bin:$PATH"

# คัดลอกไฟล์ requirements.txt และติดตั้ง Python Libraries (edge-tts)
COPY requirements.txt /var/www/html/
RUN pip install --no-cache-dir -r /var/www/html/requirements.txt

# ตั้งค่า Working Directory
WORKDIR /var/www/html

# คัดลอก Source Code ทั้งหมดเข้าสู่ Container
COPY . /var/www/html/

# ตั้งค่า Permission ให้ Apache อ่านเขียนไฟล์ได้
RUN chown -R www-data:www-data /var/www/html

# กำหนด Environment Variable ให้ PHP รู้ว่า Python อยู่ที่ไหน
ENV PYTHON_PATH="$VIRTUAL_ENV/bin/python"

# เปิด Port 80
EXPOSE 80
