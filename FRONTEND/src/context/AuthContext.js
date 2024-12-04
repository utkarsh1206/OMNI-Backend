// AuthContext.js
import React, { createContext, useState, useEffect } from 'react'
import axios from 'axios'
import { jwtDecode } from 'jwt-decode'
import { BASE_URL } from '../config'

// Create Context
export const AuthContext = createContext()

// Provider Component
export const AuthProvider = ({ children }) => {
  const [authToken, setAuthToken] = useState(() => localStorage.getItem('token') || null)
  const [user, setUser] = useState(null)
  const [brandingInfo, setBrandingInfo] = useState(null)

  const fetchBrandingInfo = async (token) => {
    try {
      const response = await axios.get(`${BASE_URL}/api/my-profile`, {
        headers: {
          'Content-Type': 'application/json',
          Authorization: `Bearer ${token}`,
        },
      })
      setBrandingInfo(response.data.data.branding_information)
      document.title = response.data.data.branding_information.title
    } catch (error) {
      console.error('Error fetching tenant data:', error)
    }
  }

  // Function to handle login
  const login = async (credentials) => {
    try {
      const response = await axios.post(`${BASE_URL}/api/login`, credentials, {
        headers: { 'Content-Type': 'application/json', 'Access-Control-Allow-Origin': '*' },
      })
      const token = response.data.token
      setAuthToken(token)
      localStorage.setItem('token', token)

      const decoded = jwtDecode(token)
      setUser({
        username: decoded.username,
        email: decoded.email,
        role_id: decoded.role_id,
        phone: decoded.phone,
        domain: decoded.domain,
      })

      await fetchBrandingInfo(token)
    } catch (error) {
      console.error('Login failed:', error)
      throw error
    }
  }

  // Function to handle logout
  const logout = () => {
    setAuthToken(null)
    setUser(null)
    setBrandingInfo(null)
    localStorage.removeItem('token')
    document.title = 'NEOX Contact Center'
  }

  // Optional: Fetch user info and branding data on mount if token exists
  useEffect(() => {
    if (authToken) {
      const isExpired = isTokenExpired(authToken)
      if (isExpired) {
        logout()
      } else {
        const decoded = jwtDecode(authToken)
        setUser({
          username: decoded.username,
          email: decoded.email,
          role_id: decoded.role_id,
          phone: decoded.phone,
          domain: decoded.domain,
        })
        fetchBrandingInfo(authToken)
      }
    }
  }, [authToken])

  // Function to check if token is expired
  const isTokenExpired = (token) => {
    try {
      const decoded = jwtDecode(token)
      return decoded.exp < Date.now() / 1000
    } catch (error) {
      console.error('Invalid token:', error)
      return true
    }
  }

  return (
    <AuthContext.Provider value={{ authToken, user, brandingInfo, login, logout }}>
      {children}
    </AuthContext.Provider>
  )
}

// -----------------------------------------------------------------------

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

// -----------------------------------------------------------------------

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

// -----------------------------------------------------------------------
