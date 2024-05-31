import Empty from './pages/NoSettings'
import Settings from './pages/Settings'

Nova.booting((app, store) => {
    Nova.inertia('Settings', Settings)
    Nova.inertia('Empty', Empty)
})
