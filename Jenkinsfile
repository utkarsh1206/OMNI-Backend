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
        emailext(
            to: 'neox.tu@stl.tech',
            from: 'prajapati.hitesh@stl.tech',
            subject: "✅ SUCCESS: ${env.JOB_NAME} Build #${env.BUILD_NUMBER}",
            body: """
                <p><b>Build Successful!</b></p>
                <ul>
                    <li>Job: ${env.JOB_NAME}</li>
                    <li>Build Number: ${env.BUILD_NUMBER}</li>
                    <li>Status: SUCCESS</li>
                    <li>Duration: ${currentBuild.durationString}</li>
                    <li>Build URL: <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></li>
                </ul>
            """,
            mimeType: 'text/html',
            attachmentsPattern: '**/log.txt'
        )
    }

    failure {
        emailext(
            to: 'neox.tu@stl.tech',
            from: 'prajapati.hitesh@stl.tech',
            subject: "❌ FAILURE: ${env.JOB_NAME} Build #${env.BUILD_NUMBER}",
            body: """
                <p><b>Build Failed!</b></p>
                <ul>
                    <li>Job: ${env.JOB_NAME}</li>
                    <li>Build Number: ${env.BUILD_NUMBER}</li>
                    <li>Status: FAILURE</li>
                    <li>Duration: ${currentBuild.durationString}</li>
                    <li>Build URL: <a href="${env.BUILD_URL}">${env.BUILD_URL}</a></li>
                </ul>
                <p>Attached: Log file for detailed error info.</p>
            """,
            mimeType: 'text/html',
            attachmentsPattern: '**/log.txt'
        )
    }
}

}
