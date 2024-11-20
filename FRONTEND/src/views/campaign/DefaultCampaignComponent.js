import { CCol, CFormLabel, CFormSelect } from '@coreui/react'
import React from 'react'

function DefaultCampaignComponent({ formData, handleChange }) {

   // const [ campaign, setCampaign ] = useState([])
  // const [loadingCampaign, setLoadingCampaign] = useState(false)
  //  // Fetch Campaigns on component mount
  //  useEffect(() => {
  //   const fetchCampaign = async () => {
  //     setLoadingCampaign(true)
  //     try {
  //       const response = await axios.get(`${BASE_URL}/api/Campaigns?page=all`)
  //       setCampaign(response.data.data)
  //       setLoadingCampaign(false)
  //     } catch (err) {
  //       setError('Error fetching Campaigns')
  //       setLoadingCampaign(false)
  //     }
  //   }
  //   fetchCampaign()
  // }, [])

  return (
    <CCol md="4" className="mb-3">
      <CFormLabel htmlFor="campaign_id">
        Campaign <span style={{ color: 'red' }}>*</span>
      </CFormLabel>
      <CFormSelect
        id="campaign_id"
        name="campaign_id"
        value={formData.campaign_id}
        onChange={handleChange}
      >
        <option value="" disabled>
          Select Campaign
        </option>
        <option key="Campaign1" value="1">
          Default Campaign
        </option>
        {/* <option key="Campaign2" value="2">
          Campaign 2
        </option> */}
        {/* Add more campaign options as needed */}
      </CFormSelect>
    </CCol>
  )
}

export default DefaultCampaignComponent
