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

    post  {
    success {
        echo "🟢 SUCCESS block triggered"
        emailext(
            subject: "✅ SUCCESS: Jenkins Pipeline - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: """<p>Great news! The pipeline for <strong>${env.JOB_NAME}</strong> build <strong>#${env.BUILD_NUMBER}</strong> has passed.</p>
                     <p>Check console output <a href="${env.BUILD_URL}console">here</a>.</p>""",
            to: 'neox.tu@stl.tech',
            attachLog: true
        )
        echo "✅ Email command executed for SUCCESS"
    }

    failure {
        echo "🔴 FAILURE block triggered"
        emailext(
            subject: "❌ FAILED: Jenkins Pipeline - ${env.JOB_NAME} #${env.BUILD_NUMBER}",
            body: """<p>Oops! The pipeline for <strong>${env.JOB_NAME}</strong> build <strong>#${env.BUILD_NUMBER}</strong> has failed.</p>
                     <p>Check console output <a href="${env.BUILD_URL}console">here</a>.</p>""",
            to: 'neox.tu@stl.tech',
            attachLog: true
        )
        echo "❌ Email command executed for FAILURE"
    }

    always {
        echo "🔁 ALWAYS block triggered. Email attempt (success/failure) should have been made."
    }
}

}
