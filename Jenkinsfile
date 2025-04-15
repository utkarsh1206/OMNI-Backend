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
        always {
        emailext(
            to: 'neox.tu@stl.tech',
            from: 'utkarsh.rokade@stl.tech',
            subject: "Build ${env.BUILD_NUMBER} Status: ${currentBuild.currentResult}",
            body: "Build completed. View it at: ${env.BUILD_URL}"
        )
    }
}

}
