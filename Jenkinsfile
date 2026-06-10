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
                // Mengambil kode terbaru dari repositori GitHub
                checkout scm
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    echo "Membangun Docker Image dengan Tag: build-${BUILD_NUMBER}"
                    // Membuat image dengan tag nomor build dan tag 'latest'
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER} ."
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest ."
                }
            }
        }

        stage('Push Image to Docker Hub') {
            steps {
                script {
                    echo 'Mengunggah Image ke Docker Hub...'
                    // Menggunakan kredensial Docker Hub yang terdaftar di Jenkins
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
                    echo 'Menghentikan container lama dan menjalankan stack terbaru via Docker Compose...'
                    // Menggunakan biner docker-compose milik EC2 yang sudah di-mount ke Jenkins
                    sh "docker-compose down"
                    sh "docker-compose up -d --build"
                    echo '=== DEPLOYMENT BERHASIL SELESAI ==='
                }
            }
        }
    }

    post {
        always {
            echo 'Membersihkan sisa build (dangling images) untuk menghemat ruang disk EC2...'
            sh 'docker image prune -f'
        }
    }
}
