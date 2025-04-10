pipeline {
    agent any

    environment {
        SONAR_AUTH_TOKEN = credentials('sonarqube-user')
    }

    stages {
        stage('Checkout') {
            steps {
                checkout scm
            }
        }

        stage('SonarQube Analysis') {
            steps {
                script {
                    def scannerHome = tool name: 'NEOX-Sonarscanner', type: 'hudson.plugins.sonar.SonarRunnerInstallation'
                    withSonarQubeEnv('NEOX-Sonarqube') {
                        sh "${scannerHome}/bin/sonar-scanner"
                    }
                }
            }
        }
    }

    post {
        always {
            emailext(
                subject: "Test Email from Jenkins",
                body: "This is a test email from Jenkins pipeline.",
                to: 'neox.tu@stl.tech',
                attachLog: true
            )
        }
    }
}
