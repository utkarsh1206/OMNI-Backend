import React, { createContext, useState, useEffect } from 'react'
import axios from 'axios'
import { jwtDecode } from 'jwt-decode'
import { BASE_URL } from '../config'

// Create Context
export const AuthContext = createContext()

// Provider Component
export const AuthProvider = ({ children }) => {
  const [authToken, setAuthToken] = useState(() => {
    return localStorage.getItem('token') || null
  })

  const [user, setUser] = useState(() => {
    const token = localStorage.getItem('token')
    if (token) {
      try {
        const decoded = jwtDecode(token)
        // return decoded.user; // Adjust this based on your token's payload structure
        return {
          username: decoded.username,
          email: decoded.email,
          role_id: decoded.role_id,
          phone: decoded.phone,
          domain: decoded.domain,
        }
      } catch (error) {
        console.error('Invalid token:', error)
        return null
      }
    }
    return null
  })

  // Function to handle login
  const login = async (credentials) => {
    try {
      const response = await axios.post(`${BASE_URL}/api/login`, credentials, {
        headers: {
          'Content-Type': 'application/json',
          'Access-Control-Allow-Origin': '*',
        },
        // withCredentials: true // if you are using cookies
      })
      const token = response.data.token
      setAuthToken(token)
      localStorage.setItem('token', token)

      // Decode the token to get user information
      const decoded = jwtDecode(token)
      // setUser(decoded.user); // Adjust based on your token's payload
      setUser({
        username: decoded.username,
        email: decoded.email,
        role_id: decoded.role_id,
        phone: decoded.phone,
        domain: decoded.domain,
      })
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    }
  }

  // Function to handle logout
  const logout = () => {
    setAuthToken(null)
    setUser(null)
    localStorage.removeItem('token')
  }

  // try to use
  // try to use below
  // Function to check if token is expired
  const isTokenExpired = (token) => {
    try {
      const decoded = jwtDecode(token)
      const currentTime = Date.now() / 1000 // Current time in seconds
      return decoded.exp < currentTime
    } catch (error) {
      console.error('Invalid token:', error)
      return true
    }
  }
  // try to use above
  // try to use

  // Optional: Refresh user info if token changes
  // useEffect(() => {
  //   if (authToken) {
  //     try {
  //       const decoded = jwtDecode(authToken)
  //       // setUser(decoded.user); // Adjust based on your token's payload
  //       setUser({
  //         username: decoded.username,
  //         email: decoded.email,
  //         role_id: decoded.role_id,
  //         phone: decoded.phone,
  //       })
  //     } catch (error) {
  //       console.error('Failed to decode token:', error)
  //       setUser(null)
  //       logout()
  //     }
  //   }
  // }, [authToken])

  // Try to use
  // Try to use below

  // Optional: Fetch user info on mount if token exists
  useEffect(() => {
    if (authToken) {
      // Example: Decode token or fetch user data
      // setUser(decodedUser);
      if (isTokenExpired(authToken)) {
        console.log('Token expired')
        logout()
      } else {
        try {
          const decoded = jwtDecode(authToken)
          // setUser(decoded.user); // Adjust based on your token's payload
          setUser({
            username: decoded.username,
            email: decoded.email,
            role_id: decoded.role_id,
            phone: decoded.phone,
            domain: decoded.domain,
          })
        } catch (error) {
          console.error('Failed to decode token:', error)
          setUser(null)
          logout()
        }
      }
    }
  }, [authToken])

  // // Axios interceptor to handle 401 errors
  // useEffect(() => {
  //   const interceptor = axios.interceptors.response.use(
  //     (response) => response,
  //     (error) => {
  //       if (error.response && error.response.status === 401) {
  //         // Unauthorized, possibly token expired
  //         logout();
  //         // Optionally, redirect to login page or show a notification
  //         // import { useNavigate } from 'react-router-dom';
  //         navigate('/login');
  //       }
  //       return Promise.reject(error);
  //     }
  //   );

  //   // Cleanup the interceptor on unmount
  //   return () => {
  //     axios.interceptors.response.eject(interceptor);
  //   };
  //   // }, [logout, navigate]);
  // }, [logout]);

  // Try to use above
  // Try to use

  // const [brandingDetails, setBrandingDetails] = useState({
  //   title: 'NEOX Contact Center',
  //   footer_content: 'CoreUI React Admin & Dashboard',
  //   footer_url: 'https://coreui.io/react',
  //   tenant_logo: '../src/assets/brand/new_menu_logo.png',
  // })
  // useEffect(() => {
  //   if (user && user.role_id == 1) {
  //     axios
  //       .get(`${BASE_URL}/api/my-profile`, {
  //         headers: {
  //           'Content-Type': 'application/json',
  //           'Access-Control-Allow-Origin': '*',
  //           Authorization: `Bearer ${authToken}`,
  //         },
  //       })
  //       .then((response) => {
  //         setBrandingDetails({
  //           title: response.data.data.branding_information.title,
  //           footer_content: response.data.data.branding_information.footer_content,
  //           footer_url: response.data.data.branding_information.footer_url,
  //           tenant_logo: response.data.data.branding_information.tenant_logo,
  //         })
  //         document.title = response.data.data.branding_information.title
  //       })
  //       .catch((error) => {
  //         console.error('Error fetching tenant data:', error)
  //       })
  //   }
  // }, [])

  return (
    <AuthContext.Provider value={{ authToken, user, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}
