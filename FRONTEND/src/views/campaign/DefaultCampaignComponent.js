import { CCol, CFormLabel, CFormSelect } from '@coreui/react'
import React from 'react'
import { CAMPAIGNS } from './../../config'

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
        Campaign/Department <span style={{ color: 'red' }}>*</span>
      </CFormLabel>
      <CFormSelect
        id="campaign_id"
        name="campaign_id"
        value={formData.campaign_id}
        onChange={handleChange}
      >
        <option value="" disabled>
          Select Campaign/Department
        </option>
        {CAMPAIGNS.map(campaign => (
          <option key={campaign.name} value={campaign.id}>
            {campaign.name}
          </option>
        ))}
      </CFormSelect>
    </CCol>
  )
}

export default DefaultCampaignComponent
