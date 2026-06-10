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
                    echo 'Menghentikan container lama dan menjalankan stack terbaru...'
                    
                    // Kita panggil biner docker utama yang dilewati flag compose agar container Jenkins 
                    // tidak perlu mencari biner 'docker-compose' eksternal yang terpisah.
                    sh "docker compose down"
                    sh "docker compose up -d --build"
                    
                    echo '=== DEPLOYMENT BERHASIL SELESAI VIA DOCKER V2 ==='
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
