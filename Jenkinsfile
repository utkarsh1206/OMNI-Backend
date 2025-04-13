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
        updateGitlabCommitStatus name: 'build', state: 'success'
        emailext attachLog: true, body: 'Email sent out from Jenkins', subject: '$PROJECT_NAME - Build # $BUILD_NUMBER - $BUILD_STATUS!', to: 'neox.tu@stl.tech'
        echo "✅ Email command executed for SUCCESS"
    }

    failure {
        echo "🔴 FAILURE block triggered"
        updateGitlabCommitStatus name: 'build', state: 'success'
        emailext attachLog: true, body: 'Email sent out from Jenkins', subject: '$PROJECT_NAME - Build # $BUILD_NUMBER - $BUILD_STATUS!', to: 'neox.tu@stl.tech'
        echo "❌ Email command executed for FAILURE"
    }

    always {
        echo "🔁 ALWAYS block triggered. Email attempt (success/failure) should have been made."
    }
}

}
