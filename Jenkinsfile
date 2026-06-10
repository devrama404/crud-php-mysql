stage('Deploy Application') {
            steps {
                script {
                    echo 'Menghentikan dan menghapus container lama jika ada...'
                    sh "docker stop nginx_webserver php_app mysql_db || true"
                    sh "docker rm nginx_webserver php_app mysql_db || true"
                    
                    echo 'Membuat jaringan internal Docker...'
                    sh "docker network create app-network || true"
                    
                    echo 'Menjalankan container Database (MySQL)...'
                    sh """
                        docker run -d \
                        --name mysql_db \
                        --network app-network \
                        -e MYSQL_DATABASE=crud_db \
                        -e MYSQL_ROOT_PASSWORD=rootpassword \
                        -e MYSQL_USER=user \
                        -e MYSQL_PASSWORD=userpassword \
                        -v dbdata:/var/lib/mysql \
                        -p 3306:3306 \
                        --restart unless-stopped \
                        mysql:8.0
                    """
                    
                    echo 'Menjalankan container Aplikasi (PHP-FPM)...'
                    sh """
                        docker run -d \
                        --name php_app \
                        --network app-network \
                        -e DB_HOST=mysql_db \
                        -e DB_DATABASE=crud_db \
                        -e DB_USERNAME=user \
                        -e DB_PASSWORD=userpassword \
                        --restart unless-stopped \
                        ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest
                    """
                    
                    echo 'Menjalankan container Web Server (Nginx)...'
                    // Kita jalankan Nginx kosongan terlebih dahulu
                    sh """
                        docker run -d \
                        --name nginx_webserver \
                        --network app-network \
                        -p 8080:80 \
                        --restart unless-stopped \
                        nginx:alpine
                    """

                    echo 'Menyuntikkan konfigurasi nginx.conf langsung ke dalam container...'
                    // Trik cerdas: Kita buat file konfigurasi langsung ke dalam container Nginx agar aman dari error path
                    sh """
                        docker exec nginx_webserver sh -c 'cat << "EOF" > /etc/nginx/conf.d/default.conf
server {
    listen 80;
    index index.php index.html;
    error_log  /var/log/nginx/error.log;
    access_log /var/log/nginx/access.log;
    root /var/www/html;

    location ~ \\.php\$ {
        try_files \$uri =404;
        fastcgi_split_path_info ^(.+\\.php)(/.+)\$;
        fastcgi_pass php_app:9000;
        fastcgi_index index.php;
        include fastcgi_params;
        fastcgi_param SCRIPT_FILENAME \$document_root\$fastcgi_script_name;
        fastcgi_param PATH_INFO \$fastcgi_path_info;
    }

    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
        gzip_static on;
    }
}
EOF'
                    """
                    
                    echo 'Mereload konfigurasi Nginx...'
                    sh "docker exec nginx_webserver nginx -s reload"
                    
                    echo '=== DEPLOYMENT BERHASIL SELESAI 100% ==='
                }
            }
        }
