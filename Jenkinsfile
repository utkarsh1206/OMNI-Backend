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
        success {
            emailext(
                subject: "✅ SUCCESS: Jenkins Pipeline - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                body: """<p>Great news! The pipeline for <strong>${env.JOB_NAME}</strong> build <strong>#${env.BUILD_NUMBER}</strong> has passed.</p>
                         <p>Check console output <a href="${env.BUILD_URL}console">here</a>.</p>""",
                to: 'prajapati.hitesh@stl.tech,chintan.chavda@stl.tech,laxman.sonwane@stl.tech,vishal.rana@stl.tech',
                attachLog: true
            )
        }

        failure {
            emailext(
                subject: "❌ FAILED: Jenkins Pipeline - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
                body: """<p>Oops! The pipeline for <strong>${env.JOB_NAME}</strong> build <strong>#${env.BUILD_NUMBER}</strong> has failed.</p>
                         <p>Check console output <a href="${env.BUILD_URL}console">here</a>.</p>""",
                to: 'prajapati.hitesh@stl.tech,chintan.chavda@stl.tech,laxman.sonwane@stl.tech,vishal.rana@stl.tech',
                attachLog: true
            )
        }

        always {
            echo "Pipeline finished. Email notification sent."
        }
    }
}
