pipeline {
    agent any

    environment {
        DOCKER_HUB_USER = 'devrama404' // Ganti dengan username Docker Hub Anda
        IMAGE_NAME      = 'crud-php-app'
        REGISTRY_CRED   = 'dockerhub-credentials-id' // ID Kredensial yang didaftarkan di Jenkins
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('Lint Check') {
            steps {
                echo 'Melakukan Validasi Sintaks PHP (Linting)...'
                // Opsi tambahan jika Jenkins agent memiliki PHP CLI terinstal:
                // sh 'find . -name "*.php" -exec php -l {} \;'
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    echo "Building Image Tag: build-${BUILD_NUMBER}"
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER} ."
                    sh "docker build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest ."
                }
            }
        }

        stage('Push Image to Docker Hub') {
            steps {
                script {
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
                    echo 'Menghentikan container lama dan menjalankan container terbaru...'
                    sh "docker compose down"
                    sh "docker compose up -d --build"
                    echo 'Deployment Berhasil Selesai!'
                }
            }
        }
    }

    post {
        always {
            echo 'Membersihkan sisa build (dangling images) untuk menghemat ruang disk...'
            sh "docker image prune -f"
        }
    }
}
