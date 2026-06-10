pipeline {
    agent any

    environment {
        DOCKER_HUB_USER = 'devrama404'
        IMAGE_NAME      = 'crud-php-app'
        REGISTRY_CRED   = 'dockerhub-credentials-id'

        // FIX PENTING: pakai full path docker
        DOCKER_BIN      = '/usr/bin/docker'
    }

    stages {

        stage('Checkout') {
            steps {
                cleanWs()
                checkout scm
            }
        }

        stage('Lint Check') {
            steps {
                echo 'Melakukan Validasi Sintaks PHP...'
                sh '''
                    find . -name "*.php" -exec php -l {} \;
                '''
            }
        }

        stage('Build Docker Image') {
            steps {
                script {
                    echo "Building Image Tag: build-${BUILD_NUMBER}"

                    sh """
                        ${DOCKER_BIN} build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER} .
                        ${DOCKER_BIN} build -t ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest .
                    """
                }
            }
        }

        stage('Push Image to Docker Hub') {
            steps {
                script {
                    withCredentials([usernamePassword(
                        credentialsId: "${REGISTRY_CRED}",
                        passwordVariable: 'DOCKER_PASSWORD',
                        usernameVariable: 'DOCKER_USER'
                    )]) {
                        sh """
                            echo \$DOCKER_PASSWORD | ${DOCKER_BIN} login -u \$DOCKER_USER --password-stdin
                            ${DOCKER_BIN} push ${DOCKER_HUB_USER}/${IMAGE_NAME}:build-${BUILD_NUMBER}
                            ${DOCKER_BIN} push ${DOCKER_HUB_USER}/${IMAGE_NAME}:latest
                        """
                    }
                }
            }
        }

        stage('Deploy Application') {
            steps {
                script {
                    echo 'Deploying container with Docker Compose V2...'

                    sh """
                        set -e
                        ${DOCKER_BIN} compose down
                        ${DOCKER_BIN} compose up -d --build
                    """

                    echo 'Deployment Berhasil Selesai!'
                }
            }
        }
    }

    post {
        always {
            echo 'Cleaning dangling images...'
            sh "${DOCKER_BIN} image prune -f"
        }
    }
}
