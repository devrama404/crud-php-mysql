pipeline {
    agent any

    environment {
        DOCKER_HUB_USER = 'devrama404'
        IMAGE_NAME      = 'crud-php-app'
        REGISTRY_CRED   = 'dockerhub-credentials-id'
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    echo "Membangun Docker Image dengan Tag: build-${BUILD_NUMBER}"
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER} ."
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest ."
                }
            }
        }

        stage('Push Image to Docker Hub') {
            steps {
                script {
                    echo 'Mengunggah Image ke Docker Hub...'
                    withCredentials([usernamePassword(credentialsId: "${REGISTRY_CRED}", passwordVariable: 'DOCKER_PASSWORD', usernameVariable: 'DOCKER_USER')]) {
                        sh "echo \$DOCKER_PASSWORD | docker login -u \$DOCKER_USER --password-stdin"
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER}"
                        sh "docker push ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest"
                    }
                }
            }
        }

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
                    
                    echo 'Menjalankan container Web Server (Nginx) kosongan...'
                    sh """
                        docker run -d \
                        --name nginx_webserver \
                        --network app-network \
                        -p 8080:80 \
                        --restart unless-stopped \
                        nginx:alpine
                    """

                    echo 'Menyuntikkan konfigurasi nginx.conf langsung ke dalam container...'
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
    }

    post {
        always {
            echo 'Membersihkan sisa build (dangling images) untuk menghemat ruang disk...'
            sh 'docker image prune -f'
        }
    }
}
